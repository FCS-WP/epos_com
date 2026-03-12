<?php
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
