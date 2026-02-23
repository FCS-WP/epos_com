<?php

function  update_hubspot_contact($token, $properties)
{


  $update_url = 'https://api.hubapi.com/crm/v3/objects/contacts/' . $properties['email'] . '?idProperty=email';

  $response = wp_remote_request($update_url, [
    'method'    => 'PATCH',
    'headers'   => [
      'Content-Type'  => 'application/json',
      'Authorization' => 'Bearer ' . $token,
    ],
    'body'      => json_encode(['properties' => $properties]),
    'timeout'   => 15,
  ]);

  return $response;
}

function  create_hubspot_contacts($token, $properties)
{
  $create_url = 'https://api.hubapi.com/crm/v3/objects/contacts';
  $response =  wp_remote_post($create_url, [
    'headers'   => [
      'Content-Type'  => 'application/json',
      'Authorization' => 'Bearer ' . $token,
    ],
    'body'      => json_encode(['properties' => $properties]),
    'timeout'   => 15,
  ]);

  return $response;
}


function create_hubspot_deal($access_token, $deal_data, $contact_id)
{
  $deal_url = 'https://api.hubapi.com/crm/v3/objects/deals';

  $deal_data = [
    'properties' => [
      'dealname'   => $deal_data['properties']['dealname'],
      'amount'     => $deal_data['properties']['amount'],
      'closedate'  => $deal_data['properties']['closedate'],
      'dealstage'  => $deal_data['properties']['dealstage'],
      'referral_code' => $deal_data['properties']['referral_code'],
      'pipeline'   => $deal_data['properties']['pipeline'],
      'pi_number' => $deal_data['properties']['pi_number'],
    ],
    'associations' => [
      [
        'to' => ['id' => $contact_id],
        'types' => [
          [
            'associationCategory' => 'HUBSPOT_DEFINED',
            'associationTypeId'   => 3  // Contact to Deal ID
          ]
        ]
      ]
    ]
  ];
  wp_remote_post($deal_url, [
    'headers' => [
      'Content-Type'  => 'application/json',
      'Authorization' => 'Bearer ' . $access_token,
    ],
    'body'    => json_encode($deal_data),
    'timeout' => 20,
  ]);
}


function get_products_data($order)
{
  $items = [];

  foreach ($order->get_items() as $item) {
    if ($item instanceof WC_Order_Item_Product) {
      $items[] = sprintf(
        '%s (x%d)',
        $item->get_name(),
        $item->get_quantity()
      );
    }
  }

  return implode("\n", $items);
}
