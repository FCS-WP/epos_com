<?php

/**
 * Sync WooCommerce Order Data to HubSpot Contacts API with UTM & Address
 */

add_action('woocommerce_checkout_order_processed', 'sync_wc_order_to_hubspot', 20, 3);

function sync_wc_order_to_hubspot($order_id, $posted_data, $order)
{

  if (!$order instanceof WC_Order) {
    return;
  }

  $hubspot_access_token =  get_field('hubspot_token', 'option');

  $payment_status = $order->get_status() == 'processing' || $order->get_status() == 'completed' ? 'PAID' : 'INITIATED CHECKOUT';

  $email      = $order->get_billing_email();
  $first_name = $order->get_billing_first_name();
  $last_name  = $order->get_billing_last_name();
  $phone      = $order->get_billing_phone();
  $company    = $order->get_billing_company();
  $address    = $order->get_billing_address_1() . ',' . $order->get_billing_address_2();
  $city       = $order->get_billing_city();
  $zip        = $order->get_billing_postcode();
  $total        = $order->get_total();
  $order_notes = $order->get_customer_note();

  $country_code = $order->get_billing_country();
  $state_code   = $order->get_billing_state();
  $states       = WC()->countries->get_states($country_code);
  $state_name   = $states[$state_code] ?? $state_code;

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
    'utm_campaign'     => $utm_campaign,
    'utm_medium'     => $utm_medium,
    "hs_latest_source" => "OTHER_CAMPAIGNS"
  ];

  //Try to update first, if 404 then create

  $response = update_hubspot_contact($hubspot_access_token, $properties);

  // If contact not found (404), create new one
  if (wp_remote_retrieve_response_code($response) == 404) {

    $response = create_hubspot_contacts($hubspot_access_token, $properties);
  }

  if (is_wp_error($response)) {
    error_log('HubSpot Sync Error: ' . $response->get_error_message());
  }
}

