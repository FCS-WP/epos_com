<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();
?>
<!-- Section: Demo -->
<section class="sub-v2-demo" id="sub-v2-demo" data-sub-v2-demo>
  <div class="sub-shell">
    <div class="sub-v2-demo__head" data-animate-group="section-head">
      <p class="sub-v2-demo__eyebrow sub-v2-kicker">
        <img class="sub-v2-kicker__icon" src="<?php echo esc_url($sub_v2['section_kicker_icon']); ?>" alt="" aria-hidden="true" decoding="async">
        <span>Try EPOS360</span>
      </p>
      <h2 class="sub-v2-demo__title sub-v2-section-title">
        Get A Demo
        <span>See EPOS360 in action</span>
      </h2>
    </div>

    <div class="sub-v2-demo__form-wrap" data-animate="fade-up">
      <?php
      // Render our HubSpot-bridge form (inline variant — full size).
      $form_variant = 'inline';
      include __DIR__ . '/_form.php';
      ?>
    </div>
  </div>
</section>
