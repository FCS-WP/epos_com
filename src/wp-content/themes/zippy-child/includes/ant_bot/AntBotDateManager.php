<?php
/**
 * Manage and calculate date based on a single input
 */
class AntBotDateManager {
  private $original_today;
  private $today;
  private $start_yesterday;
  private $end_yesterday;
  private $first_day_of_month;
  private $three_days_ago;
  private $the_last_three_days;

  public function __construct($date = false) {
    $tz = wp_timezone();
    $this->original_today = $date ? $date : new DateTime('now', $tz);
    $this->three_days_ago = (clone $this->original_today)->modify('-3 day')->setTime(0, 0, 0);
    $this->today = (clone $this->original_today);
    $elapsed = (int)$this->today->format('j');

    // if ($elapsed == 1) {
    //   // today is the first day of the new month
    //   // so rollback to 1 day to calculate last month data
    //   $this->today->modify('-1 day');
    //   // in this case yesterday should be the same as today
    //   // so MTD run rate calculation doesn't need to exclude today explicitly
    //   $this->start_yesterday = (clone $this->today)->setTime(0, 0, 0);
    // } else {
    //   $this->start_yesterday = (clone $this->today)->modify('-1 day')->setTime(0, 0, 0);
    // }
    
    $this->start_yesterday = (clone $this->today)->modify('-1 day')->setTime(0, 0, 0);
    $this->end_yesterday = (clone $this->start_yesterday)->setTime(23, 59, 59);
    $this->first_day_of_month = (clone $this->start_yesterday)->modify('first day of this month');

    // generate the list of the last 3 days
    $this->generate_last_three_days();
  }

  public function generate_last_three_days() {
    $days = [];
    $date = (clone $this->original_today); // better safe than sorry
    $name = $date->format('M d');
    $start = (clone $date)->setTime(0, 0, 0)->getTimestamp();
    $end = $date->getTimestamp(); // the time the report is called (8:00 AM)
    $days[] = [
      'name' => $name,
      'start' => $start,
      'end' => $end
    ];
    for ($i = 1; $i <= 3; $i++) {
      $date = $date->modify('-1 day')->setTime(0, 0, 0);
      $name = $date->format('M d');
      $start = $date->getTimestamp();
      $end = $date->setTime(23, 59, 59)->getTimestamp();
      $days[] = [
        'name' => $name,
        'start' => $start,
        'end' => $end
      ];
    }
    $this->the_last_three_days = array_reverse($days);
  }

  public function get_today() {
    return $this->today;
  }

  public function get_start_yesterday() {
    return $this->start_yesterday;
  }

  public function get_end_yesterday() {
    return $this->end_yesterday;
  }

  public function get_first_day_of_month() {
    return $this->first_day_of_month;
  }

  public function get_three_days_ago() {
    return $this->three_days_ago;
  }

  public function get_original_today() {
    return $this->original_today;
  }

  public function get_last_three_days() {
    return $this->the_last_three_days;
  }

  public function get_yesterday_month() {
    return $this->start_yesterday->format('n');
  }

  public function get_yesterday_display() {
    return $this->start_yesterday->format('M d');
  }

  public function display_original_today() {
    $output = $this->original_today->format('Y-m-d H:i:s');

    return "[$output]:<br>";
  }

  public function display_month_range() {
    $start = $this->first_day_of_month->format('Y-m-d H:i:s');
    $end = $this->end_yesterday->format('Y-m-d H:i:s');

    return "[$start to $end]:<br>";
  }

  public function display_yesterday_range() {
    $start = $this->start_yesterday->format('Y-m-d H:i:s');
    $end = $this->end_yesterday->format('Y-m-d H:i:s');
    
    return "[$start to $end]:<br>";
  }

  public function display_last_3_days_range() {
    $start = $this->three_days_ago->format('Y-m-d H:i:s');
    $end = $this->original_today->format('Y-m-d H:i:s');
    
    return "[$start to $end]:<br>";
  }

  public function display_days_range_title() {
    if (empty($this->the_last_three_days)) {
      return '';
    }

    $first = $this->the_last_three_days[array_key_first($this->the_last_three_days)];
    $last = $this->the_last_three_days[array_key_last($this->the_last_three_days)];

    if (!isset($first) && !isset($last) && !isset($first['start']) && !isset($last['end'])) {
      return '';
    }

    $tz = wp_timezone();

    $start_date = (new DateTime('now', $tz))->setTimestamp($first['start']);
    $end_date = (new DateTime('now', $tz))->setTimestamp($last['end']);

    $start = $start_date->format('M d');
    $end = $end_date->format('d');

    return "($start-$end)";
  }

  public function display_days_range() {
    if (empty($this->the_last_three_days)) {
      return '';
    }

    $first = $this->the_last_three_days[array_key_first($this->the_last_three_days)];
    $last = $this->the_last_three_days[array_key_last($this->the_last_three_days)];

    if (!isset($first) && !isset($last) && !isset($first['start']) && !isset($last['end'])) {
      return '';
    }

    $tz = wp_timezone();

    $start_date = (new DateTime('now', $tz))->setTimestamp($first['start']);
    $end_date = (new DateTime('now', $tz))->setTimestamp($last['end']);

    $start = $start_date->format('Y-m-d H:i:s');
    $end = $end_date->format('Y-m-d H:i:s');

    return "[$start to $end]:<br>";
  }

  public function display_days_range_from_timestamps($ts1, $ts2) {
    if (empty($ts1) && empty($ts2)) {
      return '';
    }

    $tz = wp_timezone();

    $start_date = (new DateTime('now', $tz))->setTimestamp($ts1);
    $end_date = (new DateTime('now', $tz))->setTimestamp($ts2);

    $start = $start_date->format('Y-m-d H:i:s');
    $end = $end_date->format('Y-m-d H:i:s');

    return "[$start to $end]:<br>";
  }
}
