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

/**
 * Daily Order Sync to fetch two specific reports:
 * 1. Full Yesterday (00:00:00 to 23:59:59)
 * 2. Until 5:30 PM Today (Starting from 17:30 Yesterday)
 */
function ad_handle_daily_order_sync()
{
  if (!isset($_GET['ad_run_server_cron_sync'])) {
    return;
  }

  $tz = wp_timezone();

  $start_yesterday = new DateTime('yesterday 00:00:00', $tz);
  $end_yesterday   = new DateTime('yesterday 23:59:59', $tz);

  $start_cutoff = new DateTime('yesterday 17:30:00', $tz);
  $end_today_530 = new DateTime('today 17:30:00', $tz);

  // Fetch all orders covering the widest range to minimize database calls
  $orders = wc_get_orders(array(
    'limit'        => -1,
    'status'       => array('wc-processing', 'wc-completed'),
    'date_created' => $start_yesterday->getTimestamp() . '...' . $end_today_530->getTimestamp(),
  ));

  $report_full_yesterday = [
    'title'  => $start_yesterday->format('j M') . " (Full Day)",
    'orders' => []
  ];

  $report_until_530pm = [
    'title'  => $end_today_530->format('j M') . " (Until 5:30 PM)",
    'orders' => []
  ];

  foreach ($orders as $order) {
     if ($order instanceof OrderRefund || $order->has_status('refunded')) {
      continue;
    }
    $order_ts = $order->get_date_created()->getTimestamp();

    if ($order_ts >= $start_yesterday->getTimestamp() && $order_ts <= $end_yesterday->getTimestamp()) {
      $report_full_yesterday['orders'][] = $order;
    }

    if ($order_ts >= $start_cutoff->getTimestamp() && $order_ts <= $end_today_530->getTimestamp()) {
      $report_until_530pm['orders'][] = $order;
    }
  }

  // Generate Markdown content
  $message = ad_format_antbot_markdown($report_full_yesterday);
  $message .= "\n\n---\n\n";
  $message .= ad_format_antbot_markdown($report_until_530pm);

  // Send to Antding BOT
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
