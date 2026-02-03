<?php


/**
 * Sync WooCommerce Order to HubSpot Deal after Payment
 */

add_action('woocommerce_order_status_processing', 'sync_wc_paid_order_to_hubspot_deal', 10, 2);

function sync_wc_paid_order_to_hubspot_deal($order_id, $order)
{
  $access_token = get_field('hubspot_token', 'option');

  $contact_id = sync_hubspot_contact_for_deal($order, $access_token);

  if (!$contact_id) return;

  $deal_data = [
    'properties' => [
      'dealname'     => 'EPOS Bluetap Checkout - Order #' . $order->get_order_number(),
      'amount'       => $order->get_total(),
      'pipeline'     => '781854069',
      'dealstage'    => '1142728054',
      'pi_number'    => $order->get_order_number(),
      'merchat_name__payment_team_' => $order->get_billing_company(),
      'closedate'    => $order->get_date_completed() ? $order->get_date_completed()->format('Y-m-d\TH:i:s\Z') : current_time('Y-m-d\TH:i:s\Z'),
    ],
    'associations' => [
      [
        'to' => ['id' => $contact_id],
        'types' => [
          [
            'associationCategory' => 'HUBSPOT_DEFINED',
            'associationTypeId'   => 3 // Contact to Deal ID
          ]
        ]
      ]
    ]
  ];

  create_hubspot_deal($access_token, $deal_data, $contact_id);
}

/**
 * Helper function to Upsert Contact and return HubSpot VID/ID
 */
function sync_hubspot_contact_for_deal($order, $token)
{
  $email = $order->get_billing_email();

  $url = 'https://api.hubapi.com/crm/v3/objects/contacts/' . $email . '?idProperty=email';
  $payment_status = $order->get_status() == 'processing' || $order->get_status() == 'completed' ? 'PAID' : 'INITIATED CHECKOUT';

  $email      = $order->get_billing_email();
  $first_name = $order->get_billing_first_name();
  $last_name  = $order->get_billing_last_name();
  $phone      = $order->get_billing_phone();
  $company    = $order->get_billing_company();
  $address    = $order->get_billing_address_1() . ',' . $order->get_billing_address_2();
  $city       = $order->get_billing_city();
  $zip        = $order->get_billing_postcode();

  $country_code = $order->get_billing_country();
  $state_code   = $order->get_billing_state();
  $states       = WC()->countries->get_states($country_code);
  $state_name   = $states[$state_code] ?? $state_code;
  $total        = $order->get_total();
  $order_notes = $order->get_customer_note();


  // UTM Tracking Extraction
  $utm_source   = $order->get_meta('_wc_order_attribution_utm_source') ?: 'website';
  $utm_medium   = $order->get_meta('_wc_order_attribution_utm_medium');
  $utm_campaign = $order->get_meta('_wc_order_attribution_utm_campaign');

  // Construct HubSpot Properties
  $properties = [
    'email'          => $email,
    'firstname'      => $first_name,
    'lastname'       => $last_name,
    'phone'          => $phone,
    'company'        => $company,
    'address'        => $address,
    'city'           => $city,
    'zip'            => $zip,
    'state'          => $state_name,
    'country'        => $country_code,
    'payment_status' => $payment_status ?: 'INITIATED CHECKOUT',
    'lifecyclestage' => 'customer',
    'total_pricing' => $total,
    'product_name' => get_products_data($order),
    'message' => $order_notes,

    'utm_source'     => $utm_source ?: 'Website',
    'utm_medium'     => $utm_medium,
    'utm_campaign'     => $utm_campaign,
    "hs_latest_source" => "OTHER_CAMPAIGNS"
  ];


  $contact_data = [
    'properties' => $properties
  ];

  $response = wp_remote_request($url, [
    'method'  => 'PATCH',
    'headers' => [
      'Content-Type'  => 'application/json',
      'Authorization' => 'Bearer ' . $token,
    ],
    'body'    => json_encode($contact_data),
  ]);

  $body = json_decode(wp_remote_retrieve_body($response), true);

  if (wp_remote_retrieve_response_code($response) == 404) {

    $create_res = create_hubspot_contacts($token, $contact_data['properties']);

    $body = json_decode(wp_remote_retrieve_body($create_res), true);
  }

  return isset($body['id']) ? $body['id'] : false;
}
