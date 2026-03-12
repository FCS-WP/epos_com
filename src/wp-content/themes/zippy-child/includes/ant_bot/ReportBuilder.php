<?php
use Automattic\WooCommerce\Admin\Overrides\OrderRefund;
require_once __DIR__ . '/DateManager.php';

/**
 * Check if debug mode is on
 */
function debug_on() {
  return defined('BOT_DEBUG') && BOT_DEBUG;
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
class AntBotReportBuilder {

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
}