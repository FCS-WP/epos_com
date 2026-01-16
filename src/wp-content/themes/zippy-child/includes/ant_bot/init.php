<?php

/**
 * Signs the request and sends the message to Ant Group Bot.
 * * @param string $content The text message to be sent.
 */
function ad_send_to_ant_group_bot($content)
{

  $token = '902c37ec07b70715bd337204585b3c40cb3da46900a284ef827f674752695faf';
  $webhook_base = 'https://oapi.dingtalk.com/robot/send?access_token=' . $token;
  $secret = 'SECf29b8a52e702c60a5f24b2806e1fb539e2814d9865534dd5dfc63eae1db68ad8';

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

  $payload = array(
    'msgtype' => 'text',
    'text'    => array('content' => $content)
  );

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
  // Only execute if the specific URL parameter is present
  if (!isset($_GET['ad_run_server_cron_sync'])) {
    return;
  }

  $tz = wp_timezone();

  // Define the specific time anchors
  $yesterday_530 = new DateTime('yesterday 17:30:00', $tz);
  $today_530     = new DateTime('today 17:30:00', $tz);
  $today_600     = new DateTime('today 18:00:00', $tz);

  // Single query for the widest range
  $orders = wc_get_orders(array(
    'limit'        => -1,
    'status'       => array('wc-processing', 'wc-completed'),
    'date_created' => $yesterday_530->getTimestamp() . '...' . $today_600->getTimestamp(),
  ));

  // Initialize data buckets
  $report_full_day = ['title' => $today_530->format('j M') . " (full day)", 'orders' => []];
  $report_until_6pm = ['title' => $today_530->format('j M') . " (until 6pm)", 'orders' => []];

  foreach ($orders as $order) {
    $order_ts = $order->get_date_created()->getTimestamp();

    // Check for Full Day (5:30 PM yesterday to 5:30 PM today)
    if ($order_ts >= $yesterday_530->getTimestamp() && $order_ts <= $today_530->getTimestamp()) {
      $report_full_day['orders'][] = $order;
    }

    // Check for Until 6pm (5:30 PM yesterday to 6:00 PM today)
    if ($order_ts >= $yesterday_530->getTimestamp() && $order_ts <= $today_600->getTimestamp()) {
      $report_until_6pm['orders'][] = $order;
    }
  }

  // Generate formatted strings
  $message = ad_format_antbot_report($report_full_day);
  $message .= "\n\n";
  $message .= ad_format_antbot_report($report_until_6pm);

  // Send to Bot
  ad_send_to_ant_group_bot($message);

  wp_die('Ant Group Bot: Daily Sync Completed.');
}

// 2. THE FORMATTING LOGIC
function ad_format_antbot_report($report_data)
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
    $items_qty = $order->get_item_count();
    $total_devices += $items_qty;

    // Unit breakdown
    $unit_counts[$items_qty] = ($unit_counts[$items_qty] ?? 0) + 1;

    // Channel Source (Adjust '_channel_source' to your specific meta key)
    $source = $order->get_meta('_channel_source') ?: 'direct';
    $channels[$source] = ($channels[$source] ?? 0) + 1;

    // State breakdown
    $state_code = $order->get_billing_state();
    $state_name = WC()->countries->get_states($order->get_billing_country())[$state_code] ?? $state_code;
    $states[$state_name] = ($states[$state_name] ?? 0) + 1;
  }

  // Sort data
  arsort($channels);
  arsort($states);
  ksort($unit_counts);

  // Build Message
  $output = $report_data['title'] . "\n";
  $output .= "- Total paid orders = $total_orders\n";
  $output .= "- Total devices sold = $total_devices\n";
  $output .= "- Total sales amount = RM" . number_format($total_sales, 0) . "\n";

  // "Of X orders" unit breakdown
  $unit_strings = [];
  foreach ($unit_counts as $qty => $count) {
    $label = ($qty == 1) ? "one unit" : ($qty == 2 ? "two units" : "$qty units");
    $unit_strings[] = "$count " . ($count > 1 ? "were" : "was") . " for $label";
  }
  $output .= "- Of $total_orders orders, " . implode(", ", $unit_strings) . "\n";

  // Channel breakdown
  $channel_strings = [];
  foreach ($channels as $name => $count) {
    $channel_strings[] = "$count $name";
  }
  $output .= "- Of $total_orders orders, breakdown by Channel Source = " . implode(", ", $channel_strings) . "\n";

  // Top 5 States
  $top_states = array_slice($states, 0, 5);
  $state_strings = [];
  foreach ($top_states as $name => $count) {
    $state_strings[] = "$name ($count)";
  }
  $output .= "- Top 5 states for delivery address = " . implode(", ", $state_strings);

  return $output;
}
