<?php
use Automattic\WooCommerce\Admin\Overrides\OrderRefund;

if (!defined('BOT_DEBUG')) {
  define('BOT_DEBUG', true);
}

/**
 * Manage and calculate date based on a single input
 */
class DateManager {
  private static $input;
  private static $today;
  private static $start_yesterday;
  private static $end_yesterday;
  private static $first_day_of_month;

  public static function init($date = false) {
    $tz = wp_timezone();
    self::$input = $date ? $date : new DateTime('today 00:00:00', $tz);
    self::$today = (clone self::$input);
    $elapsed = (int)self::$today->format('j');

    if ($elapsed == 1) {
      // today is the first day of the new month
      // so rollback to 1 day to calculate last month data
      self::$today->modify('-1 day');
      // in this case yesterday should be the same as today
      // so MTD run rate calculation doesn't need to exclude today explicitly
      self::$start_yesterday = (clone self::$today);
    } else {
      self::$start_yesterday = (clone self::$today)->modify('-1 day');
    }
    
    self::$end_yesterday = (clone self::$start_yesterday)->setTime(23, 59, 59);
    self::$first_day_of_month = (clone self::$start_yesterday)
      ->modify('first day of this month')
      ->setTime(0, 0, 0);
  }

  public static function get_today() {
    return self::$today;
  }

  public static function get_start_yesterday() {
    return self::$start_yesterday;
  }

  public static function get_end_yesterday() {
    return self::$end_yesterday;
  }

  public static function get_first_day_of_month() {
    return self::$first_day_of_month;
  }

  public static function display_month_range() {
    $start = self::$first_day_of_month->format('Y-m-d H:i:s');
    $end = self::$end_yesterday->format('Y-m-d H:i:s');

    return "[$start to $end]:<br>";
  }

  public static function display_yesterday_range() {
    $start = self::$start_yesterday->format('Y-m-d H:i:s');
    $end = self::$end_yesterday->format('Y-m-d H:i:s');
    
    return "[$start to $end]:<br>";
  }

  public static function display_date_input() {
    $output = self::$input->format('Y-m-d H:i:s');

    return "[$output]:<br>";
  }
}

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

// add_action('wp_loaded', 'ad_handle_daily_order_sync');
add_action('wp_loaded', 'ad_collect_orders_and_build_report');

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

/**
 * Check if debug mode is on
 */
function debug_on() {
  return defined('BOT_DEBUG') && BOT_DEBUG;
}

/**
 * Wrapper
 */
function ad_collect_orders_and_build_report() {
  if (!isset($_GET['ad_run_server_cron_sync'])) {
    return;
  }
  
  $message = prepare_orders_and_build_report();
 
  if (debug_on()) {
    echo $message;
  } else {
    // Send to Antding BOT
    ad_send_to_ant_group_bot("Daily Sales Summary", $message);
  }

  wp_die('Ant Group Bot: Reports Synchronized Successfully.');
}

/**
 * Daily Report:
 * Send at 8:00 AM
 * Include yesterday perforamnce (00:00:00 to 23:59:59)
 * 
 * Markets: Each appears at its own section
 * - Malaysia
 * - Singapore
 * 
 * Channel aggregation rules:
 * - Show top 4 sources
 * - Other sources are grouped into "Others"
 * - Combine these into TNGD:
 *  - tngd
 *  - tngdapp
 * 
 * MTD = first day of month until yesterday 23:59:59
 * 
 * Run Rate Calculation:
 * - Expected MTD Sales = (Days Elapsed / Total Days in Month) * Monthly Target
 * - Run Rate = (Actual MTD Sales / Expected MTD Sales) * 100%
 * 
 * Example:
 *  MALAYSIA
 *  Total devices sold & paid orders: 17 | 16
 *  Channel breakdown: TNGD (7), Direct (4), googlesem,googleadwords (3), Google (1), Others (2)
 *  MTD devices sold: 200 (35% of 565 target)
 *  MTD run rate: 110%
 */
function prepare_orders_and_build_report() {
  if (debug_on()) {
    $tz = wp_timezone();
    DateManager::init(new DateTime('2026-01-23 00:00:00', $tz));
  } else {
    DateManager::init();
  }

  $first_day_of_month = DateManager::get_first_day_of_month();
  $start_yesterday    = DateManager::get_start_yesterday();
  $end_yesterday      = DateManager::get_end_yesterday();

  // Fetch all orders covering the widest range to minimize database calls
  $orders = wc_get_orders(array(
    'limit'        => -1,
    'status'       => array('wc-processing', 'wc-completed'),
    'date_created' => $first_day_of_month->getTimestamp() . '...' . $end_yesterday->getTimestamp(),
  ));

  $total_orders_since_first_of_month = [];
  $total_orders_yesterday = [];

  foreach ($orders as $order) {
    if (debug_on()) {
      if ($order->get_id() === 758) {
        $order->update_meta_data('_wc_order_attribution_utm_source', 'empire');
        // $order->save();
      }
      if ($order->get_id() === 757) {
        $order->update_meta_data('_wc_order_attribution_utm_source', 'amazon');
        // $order->save();
      }
      if ($order->get_id() === 756) {
        $order->update_meta_data('_wc_order_attribution_utm_source', 'shein');
        // $order->save();
      }
      if ($order->get_id() === 755) {
        $order->update_meta_data('_wc_order_attribution_utm_source', '(direct)');
        // $order->save();
      }
      if ($order->get_id() === 754) {
        $order->update_meta_data('_wc_order_attribution_utm_source', '(direct)');
        // $order->save();
      }
      if ($order->get_id() === 753) {
        $order->update_meta_data('_wc_order_attribution_utm_source', '(direct)');
        // $order->save();
      }
      if ($order->get_id() === 752) {
        $order->update_meta_data('_wc_order_attribution_utm_source', '(direct)');
        // $order->save();
      }
    }

    if ($order instanceof OrderRefund || $order->has_status('refunded')) {
      continue;
    }
    $order_ts = $order->get_date_created()->getTimestamp();

    if ($order_ts >= $first_day_of_month->getTimestamp() && $order_ts <= $end_yesterday->getTimestamp()) {
      $total_orders_since_first_of_month[] = $order;
    }

    if ($order_ts >= $start_yesterday->getTimestamp() && $order_ts <= $end_yesterday->getTimestamp()) {
      $total_orders_yesterday[] = $order;
    }
  }

  // Generate Markdown content
  $message = build_daily_report($total_orders_yesterday, $total_orders_since_first_of_month);

  return $message;
}

/**
 * Example:
 *  Total devices sold & paid orders: 17 | 16
 */
function collect_total_devices_and_orders($orders) {
  $total_orders = count($orders);
  $total_devices = 0;

  foreach ($orders as $order) {
    $qty = $order->get_item_count();
    $total_devices += $qty;
  }

  $output = "- Total devices sold & paid orders: $total_devices | $total_orders<br>";

  if (debug_on()) {
    $output = DateManager::display_yesterday_range() . $output;
  }

  return $output;
}

/**
 * Channel aggregation rules:
 * - Show top 4 sources
 * - Other sources are grouped into "Others"
 * - Combine these into TNGD:
 *  - tngd
 *  - tngdapp
 * 
 * Example:
 *  Channel breakdown: TNGD (7), Direct (4), googlesem,googleadwords (3), Google (1), Others (2)
 */
function collect_and_combine_channels($orders) {
  $channels = [];
  $tngd_sources = ['tngd', 'tngdapp'];

  // Collect and categorize channels
  foreach ($orders as $order) {
    $source = $order->get_meta('_wc_order_attribution_utm_source');
    $source = (!$source || $source === '(direct)') ? 'Direct' : $source;
    // Combine tngd and tndgapp into one channel
    if (in_array($source, $tngd_sources)) {
      $source = 'TNGD';
    }
    $channels[$source] = ($channels[$source] ?? 0) + $order->get_item_count();
  }
  arsort($channels);

  // Group channels beyond top 5 into "Others"
  $compound_channels = [];
  $counter = 0;
  foreach ($channels as $name => $count) {
    $counter++;
    if ($counter < 5) {
      $compound_channels[$name] = $count;
    } else {
      $compound_channels['Others'] = ($compound_channels['Others'] ?? 0) + $count;
    }
  }

  // Build string
  $output = "- Channel breakdown:";
  foreach ($compound_channels as $name => $count) {
    $output .= " $name ($count),";
  }
  $output = rtrim($output, ',') . "<br>";

  if (debug_on()) {
    $output = DateManager::display_yesterday_range() . $output;
  }

  return $output;
}

/**
 * Only possitive integer is allowed
 */
function is_monthly_target_valid($monthly_target) {
  return is_int($monthly_target) && $monthly_target > 0;
}

/**
 * MTD = first day of month until yesterday 23:59:59
 * 
 * Example:
 *  MTD devices sold: 200 (35% of 565 target)
 */
function calculate_mtd_sold_and_run_rate($orders) {
  // Monthly Target
  $monthly_target = get_field('monthly_target', 'option') ?: 565;
  $monthly_target = (int)$monthly_target;
  // MTD
  $total_devices = 0;

  foreach ($orders as $order) {
    $qty = $order->get_item_count();
    $total_devices += $qty;
  }

  $output = "- MTD devices sold: $total_devices";

  if (is_monthly_target_valid($monthly_target)) {
    $percentage = round(($total_devices / $monthly_target) * 100, 2);
    if ($percentage == 0) {
      $percentage = "less than 1";
    }
    $output .= " ($percentage% of $monthly_target target)";
  }

  $output .= "<br>";

  if (debug_on()) {
    $output = DateManager::display_month_range() . $output;
  }

  $run_rate = calculate_run_rate($total_devices, $monthly_target);
  $output .= $run_rate; 

  return $output;
}

/**
 * Run Rate Calculation:
 * - Run Rate = (Actual MTD Sales / Expected MTD Sales) * 100%
 * 
 * Notations:
 *  - Actual MTD Sales = devices sold from 1st of month -> yesterday
 *  - Expected MTD Sales = (Days Elapsed / Total Days in Month) * Monthly Target
 *   - Days Elapsed = number of days passed in the current month + today
 * 
 * Example:
 *  MTD run rate: 110%
 */
function calculate_run_rate($actual_mtd_sales, $monthly_target) {
  if (!is_monthly_target_valid($monthly_target)) {
    return "- MTD run rate: N/A (Monthly Rate was not set)<br>";
  }

  $today = DateManager::get_today();
  // Days Elapsed
  $days_elapsed = (int)$today->format('j');
  // Total Days in Month
  $total_days_in_month = (int)$today->format('t');

  // Expected MTD Sales
  $expected_mtd_sales = ($days_elapsed / $total_days_in_month) * $monthly_target;
  // Run Rate
  $run_rate = round(($actual_mtd_sales / $expected_mtd_sales) * 100, 2);

  if ($run_rate == 0) {
    $run_rate = "less than 1";
  }

  $output = "- MTD run rate: $run_rate%<br>";

  if (debug_on()) {
    $output = DateManager::display_month_range() . $output;
  }

  return $output;
}

/**
 * Example:
 *  MALAYSIA
 *  Total devices sold & paid orders: 17 | 16
 *  Channel breakdown: TNGD (7), Direct (4), googlesem,googleadwords (3), Google (1), Others (2)
 *  MTD devices sold: 200 (35% of 565 target)
 *  MTD run rate: 110%
 */
function build_daily_report($total_orders_yesterday, $total_orders_since_first_of_month) {
  $output = "**MALAYSIA**<br>";
  if (debug_on()) {
    $output = DateManager::display_date_input() . $output;
  }
  // Total devices sold & paid orders: 17 | 16
  $output .= collect_total_devices_and_orders($total_orders_yesterday);
  // Channel breakdown: TNGD (7), Direct (4), googlesem,googleadwords (3), Google (1), Others (2)
  $output .= collect_and_combine_channels($total_orders_yesterday);
  // MTD devices sold: 200 (35% of 565 target)
  // MTD run rate: 110%
  $output .= calculate_mtd_sold_and_run_rate($total_orders_since_first_of_month);

  $output .= "\n\n---\n\n";

  $output .= collect_sg_report();

  return $output;
}

/**
 * Collect SG daily report from predefined endpoint
 */
function collect_sg_report() {
  $sg_access_token = get_field('sg_report_token', 'option');

  if (!$sg_access_token) {
    return "- N/A (Missing Token)";
  }

  $host = 'https://epos.com.sg';

  if (debug_on()) {
    $host = 'http://epos_sg';
  }

  $url = "$host/wp-json/reports/v1/daily";

  $response = wp_remote_post($url, array(
    'method'    => 'POST',
    'headers'   => array(
      'Authorization' => "Bearer $sg_access_token",
      'Content-Type' => 'application/json; charset=utf-8'
    ),
    'timeout'   => 30,
    'sslverify' => false,
  ));

  if (is_wp_error($response)) {
    error_log('API request failed: ' . $response->get_error_message());
    return;
  }

  $status = wp_remote_retrieve_response_code($response);
  $body   = wp_remote_retrieve_body($response);

  if ($status !== 200) {
    error_log('API returned status ' . $status);
  }

  $data = json_decode($body, true);

  return isset($data['content']) ? $data['content'] : '';
}