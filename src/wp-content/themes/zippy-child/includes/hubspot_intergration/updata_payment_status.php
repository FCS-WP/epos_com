<?php

add_action('woocommerce_order_status_processing', 'sync_wc_paid_order_to_hubspot_contact', 10, 2);


function sync_wc_paid_order_to_hubspot_contact($order_id, $order)
{
  $hubspot_access_token = get_field('hubspot_token', 'option');


  // Data Extraction
  $email      = $order->get_billing_email();
  $first_name = $order->get_billing_first_name();
  $last_name  = $order->get_billing_last_name();
  $payment_status = 'PAID';

  $properties = [
    'email'          => $email,
    'firstname'      => $first_name,
    'lastname'       => $last_name,
    'payment_status' => $payment_status,
  ];

  $response = update_hubspot_contact($hubspot_access_token, $properties);

  // If contact not found (404), create new one
  if (wp_remote_retrieve_response_code($response) == 404) {

    $response = create_hubspot_contacts($hubspot_access_token, $properties);
  }

  if (is_wp_error($response)) {
    error_log('HubSpot Sync Error: ' . $response->get_error_message());
  }
}
