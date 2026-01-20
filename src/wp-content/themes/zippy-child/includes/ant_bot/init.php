<?php

use Automattic\WooCommerce\Admin\Overrides\OrderRefund;

/**
 * Signs the request and sends the message to Ant Group Bot.
 * * @param string $content The text message to be sent.
 */
function ad_send_to_ant_group_bot($title, $content)
{

  $token = get_field('token', 'option');
  $webhook_base = 'https://oapi.dingtalk.com/robot/send?access_token=' . $token;
  $secret = get_field('secrect', 'option');

  // Generate Timestamp in milliseconds
  $timestamp = round(microtime(true) * 1000);

  // 1. Create string to sign
  $string_to_sign = $timestamp . "\n" . $secret;

  // 2. HMAC-SHA256 (raw binary)
  $hmac = hash_hmac('sha256', $string_to_sign, $secret, true);

  // 3. Base64 and URL Encode
  $sign = urlencode(base64_encode($hmac));

  // Assemble final Webhook URL with security parameters
  $final_url = "{$webhook_base}&timestamp={$timestamp}&sign={$sign}";

  $payload = [
    'msgtype' => 'markdown',
    'markdown' => [
      'title' => $title,
      'text'  => $content
    ]
  ];

  return wp_remote_post($final_url, array(
    'method'    => 'POST',
    'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
    'body'      => json_encode($payload),
    'timeout'   => 30,
  ));
}

add_action('wp_loaded', 'ad_handle_daily_order_sync');

function ad_handle_daily_order_sync()
{
  if (!isset($_GET['ad_run_server_cron_sync'])) {
    return;
  }

  $tz = wp_timezone();


  $yesterday_530 = new DateTime('yesterday 17:30:00', $tz);
  $today_530     = new DateTime('today 17:30:00', $tz);
  $today_600     = new DateTime('today 17:30:00', $tz);


  $orders = wc_get_orders(array(
    'limit'        => -1,
    'status'       => array('wc-processing', 'wc-completed'),
    'date_created' => $yesterday_530->getTimestamp() . '...' . $today_600->getTimestamp(),
  ));

  //Title
  $report_full_day  = ['title' => $today_530->format('j M') . " (full day)", 'orders' => []];
  $report_until_6pm = ['title' => $today_530->format('j M') . " (until 6pm)", 'orders' => []];

  foreach ($orders as $order) {
    if ($order instanceof OrderRefund) {
      continue;
    }
    $order_ts = $order->get_date_created()->getTimestamp();


    if ($order_ts >= $yesterday_530->getTimestamp() && $order_ts <= $today_530->getTimestamp()) {
      $report_full_day['orders'][] = $order;
    }

    if ($order_ts >= $yesterday_530->getTimestamp() && $order_ts <= $today_600->getTimestamp()) {
      $report_until_6pm['orders'][] = $order;
    }
  }

  // Generate Markdown content
  $message = ad_format_antbot_markdown($report_full_day);
  $message .= "\n\n---\n\n";
  $message .= ad_format_antbot_markdown($report_until_6pm);


  ad_send_to_ant_group_bot("Daily Sales Summary", $message);

  wp_die('Ant Group Bot: Reports Synchronized Successfully.');
}

// Create the Markdown String
function ad_format_antbot_markdown($report_data)
{
  $orders = $report_data['orders'];
  $total_orders = count($orders);
  $total_sales = 0;
  $total_devices = 0;

  $unit_counts = [];
  $channels = [];
  $states = [];

  foreach ($orders as $order) {
    $total_sales += $order->get_total();
    $qty = $order->get_item_count();
    $total_devices += $qty;

    $unit_counts[$qty] = ($unit_counts[$qty] ?? 0) + 1;

    $source = $order->get_meta('_wc_order_attribution_utm_source') ?: 'direct';
    $channels[$source] = ($channels[$source] ?? 0) + 1;

    $state_code = $order->get_billing_state();
    $state_name = WC()->countries->get_states($order->get_billing_country())[$state_code] ?? $state_code;
    $states[$state_name] = ($states[$state_name] ?? 0) + 1;
  }

  arsort($channels);
  arsort($states);
  ksort($unit_counts);

  // Build Output using Markdown
  $output = "**" . $report_data['title'] . "**\n";
  $output .= "- Total paid orders = $total_orders\n";
  $output .= "- Total devices sold = $total_devices\n";
  $output .= "- Total sales amount = RM" . number_format($total_sales, 0) . "\n";

  // Unit breakdown
  $unit_parts = [];
  foreach ($unit_counts as $qty => $count) {
    $label = ($qty == 1) ? "one unit" : ($qty == 2 ? "two units" : "$qty units");
    $unit_parts[] = "$count " . ($count > 1 ? "were" : "was") . " for $label";
  }
  $output .= "- Of $total_orders orders, " . implode(", ", $unit_parts) . "\n";

  // Channel breakdown
  $chan_parts = [];
  foreach ($channels as $name => $count) {
    $chan_parts[] = "$count $name";
  }
  $output .= "- Of $total_orders orders, breakdown by Channel Source = " . implode(", ", $chan_parts) . "\n";

  // Top 5 States
  $top_states = array_slice($states, 0, 5);
  $state_parts = [];
  foreach ($top_states as $name => $count) {
    $state_parts[] = "$name ($count)";
  }
  $output .= "- Top " . count($top_states) . " states for delivery address = " . implode(", ", $state_parts);

  return $output;
}
