<?php
/**
 * Subscription landing — HubSpot form (rendered via our REST bridge,
 * NOT via HubSpot's iframe embed).
 *
 * Designed to be included in two places: the inline #sub-v2-demo section,
 * and the #sub-v2-demo-modal popup. Uses $form_variant ('inline' or 'modal')
 * to namespace IDs and add a variant class on the <form> for CSS targeting.
 */

if (! defined('ABSPATH')) exit;

$content              = landing_content();
$form                 = $content['form'] ?? array();
$industry_options     = $form['industry_options'] ?? array();
$state_options        = $form['state_options'] ?? array();
$language_options     = $form['language_options'] ?? array();
$interested_default   = $form['interested_product_default'] ?? 'BlueTap';
$phone_countries      = $form['phone_countries'] ?? array('my', 'sg', 'vn');
$phone_default        = $form['phone_default_country'] ?? 'my';
$success_message      = $form['success_message'] ?? 'Thanks — we will be in touch.';

// Variant — set by the including section. Defaults to inline.
$variant = isset($form_variant) && in_array($form_variant, array('inline', 'modal'), true)
    ? $form_variant : 'inline';

// Unique-id prefix so two instances on the same page (inline + modal) don't
// collide. Each input gets its own id so labels still wire correctly.
$prefix = 'ld-' . $variant . '-';

$render_options = function ($options, $placeholder, $default_value = '') {
    echo '<option value="" disabled' . ($default_value === '' ? ' selected' : '') . '>'
        . esc_html($placeholder) . '</option>';
    foreach ($options as $opt) {
        if (! is_array($opt) || empty($opt['value'])) continue;
        $value = (string) $opt['value'];
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($value),
            $value === $default_value ? ' selected' : '',
            esc_html($opt['label'] ?? $value)
        );
    }
};
?>
<div class="sub-v2-form-shell" data-form-shell>
<form
    class="sub-v2-form sub-v2-form--<?php echo esc_attr($variant); ?>"
    data-landing-form="hubspot"
    data-form-variant="<?php echo esc_attr($variant); ?>"
    data-success-message="<?php echo esc_attr($success_message); ?>"
    data-phone-countries="<?php echo esc_attr(implode(',', $phone_countries)); ?>"
    data-phone-default="<?php echo esc_attr($phone_default); ?>"
    novalidate
>
    <?php /*
      DOM order matches the MOBILE layout (single column reads top-to-bottom).
      Desktop two-column layout is achieved with CSS `order:` overrides on
      each --name modifier — see style.scss `.sub-v2-form__grid` block.
    */ ?>
    <div class="sub-v2-form__grid">
        <div class="sub-v2-form__row sub-v2-form__row--name">
            <label class="sub-v2-form__label" for="<?php echo esc_attr($prefix . 'lastname'); ?>">
                Name <span class="sub-v2-form__required" aria-hidden="true">*</span>
            </label>
            <input
                type="text"
                id="<?php echo esc_attr($prefix . 'lastname'); ?>"
                name="lastname"
                class="sub-v2-form__input"
                placeholder="Your name"
                autocomplete="name"
                required
            />
            <p class="sub-v2-form__error" data-error-for="lastname"></p>
        </div>

        <div class="sub-v2-form__row sub-v2-form__row--email">
            <label class="sub-v2-form__label" for="<?php echo esc_attr($prefix . 'email'); ?>">
                Email <span class="sub-v2-form__required" aria-hidden="true">*</span>
            </label>
            <input
                type="email"
                id="<?php echo esc_attr($prefix . 'email'); ?>"
                name="email"
                class="sub-v2-form__input"
                placeholder="Youremail@example.com"
                autocomplete="email"
                required
            />
            <p class="sub-v2-form__error" data-error-for="email"></p>
        </div>

        <div class="sub-v2-form__row sub-v2-form__row--phone">
            <label class="sub-v2-form__label" for="<?php echo esc_attr($prefix . 'phone'); ?>">
                WhatsApp Phone Number <span class="sub-v2-form__required" aria-hidden="true">*</span>
            </label>
            <input
                type="tel"
                id="<?php echo esc_attr($prefix . 'phone'); ?>"
                name="phone"
                class="sub-v2-form__input sub-v2-form__phone"
                placeholder="(+60)1234 5678"
                autocomplete="tel"
                required
            />
            <p class="sub-v2-form__error" data-error-for="phone"></p>
        </div>

        <div class="sub-v2-form__row sub-v2-form__row--industry">
            <label class="sub-v2-form__label" for="<?php echo esc_attr($prefix . 'industry'); ?>">
                Your Industry <span class="sub-v2-form__required" aria-hidden="true">*</span>
            </label>
            <select
                id="<?php echo esc_attr($prefix . 'industry'); ?>"
                name="your_industry"
                class="sub-v2-form__input sub-v2-form__select"
                required
            >
                <?php $render_options($industry_options, 'Select your industry', 'F&B'); ?>
            </select>
            <p class="sub-v2-form__error" data-error-for="your_industry"></p>
        </div>

        <div class="sub-v2-form__row sub-v2-form__row--company">
            <label class="sub-v2-form__label" for="<?php echo esc_attr($prefix . 'company'); ?>">Company Name</label>
            <input
                type="text"
                id="<?php echo esc_attr($prefix . 'company'); ?>"
                name="company"
                class="sub-v2-form__input"
                placeholder="Company name"
                autocomplete="organization"
            />
            <p class="sub-v2-form__error" data-error-for="company"></p>
        </div>

        <div class="sub-v2-form__row sub-v2-form__row--state">
            <label class="sub-v2-form__label" for="<?php echo esc_attr($prefix . 'state'); ?>">
                State / Region <span class="sub-v2-form__required" aria-hidden="true">*</span>
            </label>
            <select
                id="<?php echo esc_attr($prefix . 'state'); ?>"
                name="state_dropdown"
                class="sub-v2-form__input sub-v2-form__select"
                required
            >
                <?php $render_options($state_options, 'Region'); ?>
            </select>
            <p class="sub-v2-form__error" data-error-for="state_dropdown"></p>
        </div>


        <div class="sub-v2-form__row sub-v2-form__row--submit">
            <button type="submit" class="sub-v2-form__submit">
                <span class="sub-v2-form__submit-label"><?php echo esc_html($form['submit_label'] ?? 'Submit'); ?></span>
                <span class="sub-v2-form__submit-spinner" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <?php // Hidden: campaign default for "Interested Product" (HubSpot prop pos_typetype). ?>
    <input type="hidden" name="pos_typetype" value="<?php echo esc_attr($interested_default); ?>" />

    <?php // Honeypot — must stay empty (real users don't see it). ?>
    <div class="sub-v2-form__honeypot" aria-hidden="true">
        <label for="<?php echo esc_attr($prefix . 'website-url'); ?>">Website (leave blank)</label>
        <input type="text" id="<?php echo esc_attr($prefix . 'website-url'); ?>" name="website_url" tabindex="-1" autocomplete="off" />
    </div>

    <p class="sub-v2-form__status" role="status" aria-live="polite"></p>
</form>

    <?php // Success block — hidden by default, swapped in by JS when submit succeeds. ?>
    <div class="sub-v2-form-success" data-form-success hidden>
      <h3 class="sub-v2-form-success__title">Application Received</h3>
      <p class="sub-v2-form-success__lead">Thank you for your interest.</p>
      <p class="sub-v2-form-success__body">Our team is currently reviewing your application and will reach out to you via WhatsApp shortly.</p>
    </div>
</div><?php // /.sub-v2-form-shell ?>
