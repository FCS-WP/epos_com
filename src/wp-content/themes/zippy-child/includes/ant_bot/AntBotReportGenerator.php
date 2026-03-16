<?php
use Automattic\WooCommerce\Admin\Overrides\OrderRefund;
require_once __DIR__ . '/AntBotDateManager.php';

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
class AntBotReportGenerator {
  private $date_manager;
  private $debug_mode = false;
  private $print = false;
  private $debug_date = '';
  private $need_sg_report = false;
  private $origin_sg = false;
  private $country_name = '';

  public function __construct($name = '') {
    $this->country_name = $name;
  }

  public function turn_debug_mode_on() {
    $this->debug_mode = true;
  }

  public function turn_print_on() {
    $this->print = true;
  }

  public function set_debug_date($debug_date) {
    $this->debug_date = $debug_date;
  }

  public function turn_sg_report_on() {
    $this->need_sg_report = true;
  }

  public function set_origin_sg() {
    $this->origin_sg = true;
  }

  public function is_debug_on() {
    return $this->debug_mode;
  }

  public function is_print_on() {
    return $this->print;
  }

  public function is_origin_sg() {
    return $this->origin_sg;
  }

  public function is_sg_report_needed() {
    return $this->need_sg_report;
  }

  public function generate_report() {
    return $this->prepare_orders_and_build_report();
  }

  private function prepare_date_manager() {
    if ($this->debug_date) {
      $tz = wp_timezone();
      $this->date_manager = new AntBotDateManager(new DateTime($this->debug_date, $tz));
    } else {
      $this->date_manager = new AntBotDateManager();
    }
  }

  private function prepare_orders_and_build_report() {
    $this->prepare_date_manager();
  
    $first_day_of_month = $this->date_manager->get_first_day_of_month();
    $start_yesterday    = $this->date_manager->get_start_yesterday();
    $end_yesterday      = $this->date_manager->get_end_yesterday();
    // $three_days_ago     = $this->date_manager->get_three_days_ago();
    // $original_today     = $this->date_manager->get_original_today();

    // Fetch all orders covering the widest range to minimize database calls
    $args = [
      'limit'        => -1,
      'status'       => ['completed', 'processing', 'pending', 'failed', 'cancelled'],
      'date_created' => $first_day_of_month->getTimestamp() . '...' . $end_yesterday->getTimestamp(),
    ];

    if ($this->is_origin_sg()) {
      $args['meta_query'] = [
        [
          'key'   => 'bluetap360_order',
          'value' => 'yes'
        ]
      ];
    };

    $orders = wc_get_orders($args);
  
    // first of month -> yesterday
    $total_orders_since_first_of_month = [];
    // just yesterday
    $total_orders_yesterday = [];
    // 3 days ago -> today
    $total_orders_since_3_days_ago = [];
  
    // $ids = '';
    foreach ($orders as $order) {
      // temporarily set order data for testing purposes
      // if ($this->is_debug_on() && !$this->is_origin_sg()) {
      //   // $ids .= $order->get_id() . ' ' . $order->get_meta('bluetap360_order') . '<br>';
      //   if ($order->get_id() === 758) {
      //     $order->update_meta_data('_wc_order_attribution_utm_source', 'empire');
      //     $order->set_status('completed');
      //     // $order->save();
      //   }
      //   if ($order->get_id() === 757) {
      //     $order->update_meta_data('_wc_order_attribution_utm_source', 'amazon');
      //     $order->set_status('pending');
      //     // $order->save();
      //   }
      //   if ($order->get_id() === 756) {
      //     $order->update_meta_data('_wc_order_attribution_utm_source', 'shein');
      //     $order->set_status('failed');
      //     // $order->save();
      //   }
      //   if ($order->get_id() === 755) {
      //     $order->update_meta_data('_wc_order_attribution_utm_source', '(direct)');
      //     $order->set_status('cancelled');
      //     // $order->save();
      //   }
      //   if ($order->get_id() === 754) {
      //     $order->update_meta_data('_wc_order_attribution_utm_source', '(direct)');
      //     $order->set_status('completed');
      //     // $order->save();
      //   }
      //   if ($order->get_id() === 753) {
      //     $order->update_meta_data('_wc_order_attribution_utm_source', '(direct)');
      //     $order->set_status('pending');
      //     // $order->save();
      //   }
      //   if ($order->get_id() === 752) {
      //     $order->update_meta_data('_wc_order_attribution_utm_source', '(direct)');
      //     $order->set_status('cancelled');
      //     // $order->save();
      //   }
      // }

      if ($order instanceof OrderRefund || $order->has_status('refunded')) {
        continue;
      }
      $order_ts = $order->get_date_created()->getTimestamp();
      // first of month -> yesterday
      if ($order_ts >= $first_day_of_month->getTimestamp() && $order_ts <= $end_yesterday->getTimestamp() && in_array($order->get_status(), ['completed', 'processing'])) {
        $total_orders_since_first_of_month[] = $order;
      }
      // just yesterday
      if ($order_ts >= $start_yesterday->getTimestamp() && $order_ts <= $end_yesterday->getTimestamp() && in_array($order->get_status(), ['completed', 'processing'])) {
        $total_orders_yesterday[] = $order;
      }
      // 3 days ago -> today
      // if ($order_ts >= $three_days_ago->getTimestamp() && $order_ts <= $original_today->getTimestamp()) {
      //   $total_orders_since_3_days_ago[] = $order;
      // }
    }
  
    // Generate Markdown content
    $message = $this->build_daily_report($total_orders_yesterday, $total_orders_since_first_of_month);

    // if ($this->is_debug_on()) {
    //   $message .= $ids;
    // }
  
    return $message;
  }

  /**
   * Example:
   *  MALAYSIA
   *  Total devices sold & paid orders: 17 | 16
   *  Channel breakdown: TNGD (7), Direct (4), googlesem,googleadwords (3), Google (1), Others (2)
   *  MTD devices sold: 200 (35% of 565 target)
   *  MTD run rate: 110%
   */
  private function build_daily_report($total_orders_yesterday, $total_orders_since_first_of_month) {
    $output = "**$this->country_name**<br>";
    if ($this->is_debug_on() && $this->is_print_on()) {
      $output = $this->date_manager->display_original_today() . $output;
    }
    // Total devices sold & paid orders: 17 | 16
    $output .= $this->collect_total_devices_and_orders($total_orders_yesterday);
    // Channel breakdown: TNGD (7), Direct (4), googlesem,googleadwords (3), Google (1), Others (2)
    $output .= $this->collect_and_combine_channels($total_orders_yesterday);
    // MTD devices sold: 200 (35% of 565 target)
    // MTD run rate: 110%
    $output .= $this->calculate_mtd_sold_and_run_rate($total_orders_since_first_of_month);
    // Collecting order statuses for the last 3 days
    // $output .= $this->calculate_last_three_days_order_status_counts($total_orders_since_3_days_ago);
    // Collecting order statuses for yesterday
    $output .= $this->calculate_yesterday_order_status_counts($total_orders_yesterday);
    // Collect SG report
    if ($this->is_sg_report_needed()) {
      $output .= "\n\n---\n\n";
      $output .= $this->collect_sg_report();
    }
  
    return $output;
  }

  /**
   * Get BlueTap product count only
   */
  private function get_product_count_in_order($order) {
    $total = 0;

    if ($this->is_origin_sg()) {
      foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        // global check from ../epos_bluetap_config.php
        if (is_bluetap_product($product_id)) {
          $qty = $item->get_quantity();
          $total += $qty;
        }
      }
    } else {
      $qty = $order->get_item_count();
      $total += $qty;
    }

    return $total;
  }
  
  /**
   * Example:
   *  Total devices sold & paid orders: 17 | 16
   */
  private function collect_total_devices_and_orders($orders) {
    $total_orders = count($orders);
    $total_devices = 0;
  
    foreach ($orders as $order) {
      $total_devices += $this->get_product_count_in_order($order);
    }

    $output = "- Total devices sold & paid orders: $total_devices | $total_orders<br>";
  
    if ($this->is_debug_on() && $this->is_print_on()) {
      $output = $this->date_manager->display_yesterday_range() . $output;
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
  private function collect_and_combine_channels($orders) {
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
      $channels[$source] = ($channels[$source] ?? 0) + $this->get_product_count_in_order($order);
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
  
    if ($this->is_debug_on() && $this->is_print_on()) {
      $output = $this->date_manager->display_yesterday_range() . $output;
    }
  
    return $output;
  }
  
  /**
   * Only possitive integer is allowed
   */
  private function is_monthly_target_valid($monthly_target) {
    return is_int($monthly_target) && $monthly_target > 0;
  }
  
  /**
   * MTD = first day of month until yesterday 23:59:59
   * 
   * Example:
   *  MTD devices sold: 200 (35% of 565 target)
   */
  private function calculate_mtd_sold_and_run_rate($orders) {
    // Monthly Target
    $monthly_targets = get_field('monthly_targets', 'option') ?: [];
    $this_month = (int)$this->date_manager->get_current_month();
    $monthly_target = !empty($monthly_targets) && isset($monthly_targets[$this_month]) && isset($monthly_targets[$this_month]['target']) ? (int)$monthly_targets[$this_month]['target'] : 0;
    // MTD
    $total_devices = 0;
  
    foreach ($orders as $order) {
      $total_devices += $this->get_product_count_in_order($order);
    }
  
    $output = "- MTD devices sold: $total_devices";
  
    if ($this->is_monthly_target_valid($monthly_target)) {
      $percentage = round(($total_devices / $monthly_target) * 100, 2);
      if ($percentage == 0) {
        $percentage = "less than 1";
      }
      $output .= " ($percentage% of $monthly_target target)";
    }
  
    $output .= "<br>";
  
    if ($this->is_debug_on() && $this->is_print_on()) {
      $output = $this->date_manager->display_month_range() . $output;
    }
  
    $run_rate = $this->calculate_run_rate($total_devices, $monthly_target);
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
  private function calculate_run_rate($actual_mtd_sales, $monthly_target) {
    if (!$this->is_monthly_target_valid($monthly_target)) {
      return "- MTD run rate: N/A (Monthly Rate was not set)<br>";
    }
  
    $today = $this->date_manager->get_today();
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
  
    if ($this->is_debug_on() && $this->is_print_on()) {
      $output = $this->date_manager->display_month_range() . $output;
    }
  
    return $output;
  }

  /**
   * Prepare status count per day array
   */
  private function create_status_counter($labels) {
    $counts = array_fill_keys($labels, 0);
    $counts['Total'] = 0;
    return $counts;
  }

  /**
   * Status count for the last 3 days (last 3 days + today)
   */
  private function calculate_last_three_days_order_status_counts($orders) {
    $last_3_days = $this->date_manager->get_last_three_days();
    $order_status_counts = [];
    $status_map = [
      'completed'  => 'Completed',
      'processing' => 'Processing',
      'pending'    => 'Pending Payment',
      'failed'     => 'Failed',
      'cancelled'  => 'Cancelled',
    ];

    $labels = array_values($status_map);
    
    // Prepare the list
    foreach ($last_3_days as $day) {
      $name = $day['name'];
      $order_status_counts[$name] = $this->create_status_counter($labels);

      if ($this->is_debug_on() && $this->is_print_on()) {
        $start = $day['start'];
        $end = $day['end'];
        $date_range = $this->date_manager->display_days_range_from_timestamps($start, $end);
        $order_status_counts[$name] = ["Range" => $date_range] + $order_status_counts[$name];
      }
    }
    $total_status_counts = $this->create_status_counter($labels);

    foreach ($orders as $order) {
      $status = $order->get_status();

      if (!isset($status_map[$status])) {
        continue;
      }

      $label = $status_map[$status];
      $order_ts = $order->get_date_created()->getTimestamp();

      foreach ($last_3_days as $day) {
        $name = $day['name'];

        // Do the count
        if ($order_ts >= $day['start'] && $order_ts <= $day['end']) {
          $order_status_counts[$name][$label]++;
          $order_status_counts[$name]['Total']++;

          $total_status_counts[$label]++;
          $total_status_counts['Total']++;

          break;
        }
      }
    }

    // Add total to the list
    $order_status_counts['Total'] = $total_status_counts;

    // Output
    $date_range_title = $this->date_manager->display_days_range_title();
    $output = "- Status count for the last 3 days $date_range_title:<br>";
    foreach ($order_status_counts as $name => $status_counts) {
      $output .= "&nbsp;&nbsp;- $name:";
      if ($this->is_debug_on() && $this->is_print_on()) {
        $output .= ' ' . $status_counts['Range'];
      }
      foreach ($status_counts as $label => $count) {
        if ($label == 'Total' || $label != 'Range' && $count != 0) {
          $output .= " $count ($label),";
        }
      }
      $output = rtrim($output, ',') . "<br>";
    }

    if ($this->is_debug_on() && $this->is_print_on()) {
      $output = $this->date_manager->display_days_range() . $output;
    }

    return $output;
  }

  /**
   * Status count for yesterday
   */
  private function calculate_yesterday_order_status_counts($orders) {
    $yesterday_display  = $this->date_manager->get_yesterday_display();
    $order_status_counts = [];
    $order_status_range = '';
    $status_map = [
      'completed'  => 'Completed',
      'processing' => 'Processing',
      'pending'    => 'Pending Payment',
      'cancelled'  => 'Cancelled',
      'failed'     => 'Failed',
    ];

    $labels = array_values($status_map);

    // Prepare yesterday list
    $order_status_counts = $this->create_status_counter($labels);

    if ($this->is_debug_on() && $this->is_print_on()) {
      $order_status_range = $this->date_manager->display_yesterday_range();
    }
    
    foreach ($orders as $order) {
      $status = $order->get_status();

      if (!isset($status_map[$status])) {
        continue;
      }

      $label = $status_map[$status];

      $order_status_counts[$label]++;
      $order_status_counts['Total']++;
    }

    // Output
    $output = "- Yesterday status count ($yesterday_display): ";
    foreach ($order_status_counts as $label => $count) {
      if ($count != 0) {
        $output .= " $count ($label),";
      }
    }
    $output = rtrim($output, ',') . "<br>";

    if ($this->is_debug_on() && $this->is_print_on()) {
      $output = $order_status_range . $output;
    }

    return $output;
  }
  
  /**
   * Collect SG daily report from predefined endpoint
   */
  private function collect_sg_report() {
    $sg_access_token = get_field('sg_report_token', 'option');
    $host = get_field('sg_host', 'option');
  
    if (!$sg_access_token) {
      return "- N/A (Missing Token)";
    }

    if (!$host) {
      return "- N/A (Missing SG Host URL)";
    }
  
    // $host = 'https://epos.com.sg';
  
    // if ($this->is_debug_on()) {
    //   // docker container's name
    //   $host = 'http://epos_sg';
    // }
  
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