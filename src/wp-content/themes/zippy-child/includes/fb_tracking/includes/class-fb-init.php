<?php
class My_FB_Init
{
  public function __construct()
  {
    add_action('wp_head', array($this, 'inject_base_pixel'));
  }

  /**
   * Generate a unique Event ID for Deduplication
   */
  public static function get_event_id()
  {
    static $event_id;
    if (! isset($event_id)) {
      $event_id = bin2hex(random_bytes(16));
    }
    return $event_id;
  }

  /**
   * Insert Facebook Pixel Base Code
   */
  public function inject_base_pixel()
  {
    $pixel_id = get_my_fb_pixel_id();
    if (! $pixel_id) return;
?>

    <!-- Meta Pixel Code -->
    <script>
      ! function(f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function() {
          n.callMethod ?
            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = !0;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = !0;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s)
      }(window, document, 'script',
        'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '<?php echo esc_js($pixel_id); ?>');
      fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=977422665297634&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->
<?php
  }
}
