<?php
/**
 * Landings — HubSpot form submission HTTP client.
 *
 * Wraps the HubSpot v3 form-submission endpoint:
 *   https://api.hsforms.com/submissions/v3/integration/submit/{portalId}/{formGuid}
 *
 * Reference:
 *   https://legacydocs.hubspot.com/docs/methods/forms/submit_form_v3
 *
 * The endpoint is unauthenticated — anyone with portal+form IDs can submit.
 * That's by design (HubSpot's own embed works the same way). Spam control is
 * the consumer's responsibility (we add nonce + origin check + bot defenses
 * in class-form-api.php).
 */

if (! defined('ABSPATH')) exit;

if (! class_exists('Landings_HubSpot_Form_Client')) :

class Landings_HubSpot_Form_Client
{
    /**
     * Submit a form to HubSpot.
     *
     * @param string $portal_id   HubSpot portal/hub id (numeric, as string).
     * @param string $form_guid   HubSpot form GUID.
     * @param array  $fields      Map of field name => value (e.g. ['email' => '...', 'firstname' => '...']).
     * @param array  $context     Optional. Page context for HubSpot:
     *                              - hutk      (string) HubSpot tracking cookie value
     *                              - pageUri   (string) URL the form was submitted from
     *                              - pageName  (string) Title of that page
     *                              - ipAddress (string) Submitter's IP
     * @return array {
     *   @type bool        $success
     *   @type int|null    $status   HTTP status from HubSpot
     *   @type string|null $message  HubSpot's confirmation message on success
     *   @type array       $errors   Field-level errors on failure (if any)
     *   @type string|null $error    Top-level error message on failure
     * }
     */
    public function submit($portal_id, $form_guid, array $fields, array $context = array())
    {
        if (! $portal_id || ! $form_guid) {
            return $this->error('Missing HubSpot portal or form id.');
        }

        $url = sprintf(
            'https://api.hsforms.com/submissions/v3/integration/submit/%s/%s',
            rawurlencode($portal_id),
            rawurlencode($form_guid)
        );

        $payload = array(
            'fields'  => $this->shape_fields($fields),
            'context' => $this->shape_context($context),
        );

        $response = wp_remote_post($url, array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ),
            'body'    => wp_json_encode($payload),
            'timeout' => 10,
        ));

        if (is_wp_error($response)) {
            return $this->error('Network error: ' . $response->get_error_message());
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body   = json_decode(wp_remote_retrieve_body($response), true);

        if ($status >= 200 && $status < 300) {
            return array(
                'success' => true,
                'status'  => $status,
                'message' => isset($body['inlineMessage']) ? (string) $body['inlineMessage'] : null,
                'errors'  => array(),
                'error'   => null,
            );
        }

        // HubSpot returns structured error payloads on 4xx with `errors` array.
        $field_errors = array();
        if (is_array($body) && ! empty($body['errors']) && is_array($body['errors'])) {
            foreach ($body['errors'] as $err) {
                if (! is_array($err)) continue;
                $message = isset($err['message']) ? (string) $err['message'] : 'Invalid input';
                // HubSpot encodes field names as "fields.<name>" sometimes.
                if (! empty($err['errorType']) && $err['errorType'] === 'INVALID_EMAIL') {
                    $field_errors['email'] = $message;
                } elseif (preg_match('/fields\.([^"\'\s]+)/', $message, $m)) {
                    $field_errors[$m[1]] = $message;
                } else {
                    $field_errors['_'][] = $message;
                }
            }
        }

        return array(
            'success' => false,
            'status'  => $status,
            'message' => null,
            'errors'  => $field_errors,
            'error'   => is_array($body) && isset($body['message'])
                ? (string) $body['message']
                : 'Submission failed (HTTP ' . $status . ').',
        );
    }

    /**
     * Convert a flat ['name' => 'value'] map to HubSpot's
     * [{ "name": "...", "value": "..." }] array shape.
     *
     * @param array $fields
     * @return array
     */
    protected function shape_fields(array $fields)
    {
        $out = array();
        foreach ($fields as $name => $value) {
            if ($name === '' || $name === null) continue;
            // HubSpot expects strings; cast booleans/numbers explicitly.
            if (is_array($value)) {
                $value = implode(';', array_map('strval', $value));
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } else {
                $value = (string) $value;
            }
            $out[] = array('name' => (string) $name, 'value' => $value);
        }
        return $out;
    }

    /**
     * Build the HubSpot context block from optional inputs.
     *
     * @param array $context
     * @return array
     */
    protected function shape_context(array $context)
    {
        $shaped = array();
        if (! empty($context['hutk']))      $shaped['hutk']      = (string) $context['hutk'];
        if (! empty($context['pageUri']))   $shaped['pageUri']   = (string) $context['pageUri'];
        if (! empty($context['pageName']))  $shaped['pageName']  = (string) $context['pageName'];
        if (! empty($context['ipAddress'])) $shaped['ipAddress'] = (string) $context['ipAddress'];
        return $shaped;
    }

    /**
     * @param string $message
     * @return array
     */
    protected function error($message)
    {
        return array(
            'success' => false,
            'status'  => null,
            'message' => null,
            'errors'  => array(),
            'error'   => (string) $message,
        );
    }
}

endif;
