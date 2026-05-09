<?php
if (! defined('ABSPATH')) exit;
$sub_v2 = $data ?? array();
?>
<?php // `sub-page` is added so the form's `.sub-page .sub-v2-form` rules
      // resolve here too — the modal is rendered outside <main> (sibling of
      // it) so it isn't a descendant of the .sub-page on <main>. ?>
<div class="sub-page sub-v2-modal-demo" id="sub-v2-demo-modal" data-sub-v2-demo-modal hidden aria-hidden="true">
  <div class="sub-v2-modal-demo__backdrop" data-sub-v2-demo-modal-close></div>

  <div class="sub-v2-modal-demo__dialog" role="dialog" aria-modal="true" aria-label="Get a demo form" tabindex="-1">
    <button type="button" class="sub-v2-modal-demo__close" data-sub-v2-demo-modal-close aria-label="Close demo form">
      <span aria-hidden="true">×</span>
    </button>

    <div class="sub-v2-modal-demo__content">
      <div class="sub-v2-modal-demo__form-wrap">
        <?php
        // Render our HubSpot-bridge form (modal variant — compact).
        $form_variant = 'modal';
        include __DIR__ . '/_form.php';
        ?>
      </div>
    </div>
  </div>
</div>
