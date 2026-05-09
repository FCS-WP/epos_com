<?php
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

// Tiny helper to render a <select> with options, plus a placeholder option.
$render_options = function ($options, $placeholder) {
    echo '<option value="" disabled selected>' . esc_html($placeholder) . '</option>';
    foreach ($options as $opt) {
        if (! is_array($opt) || empty($opt['value'])) continue;
        printf(
            '<option value="%s">%s</option>',
            esc_attr($opt['value']),
            esc_html($opt['label'] ?? $opt['value'])
        );
    }
};
?>
<section id="subscription-form" class="landing__form" data-animate="fade-up">
    <div class="landing__form-inner">
        <?php if (! empty($form['section_title'])): ?>
            <h2 class="landing__section-title"><?php echo esc_html($form['section_title']); ?></h2>
        <?php endif; ?>

        <?php if (! empty($form['intro'])): ?>
            <p class="landing__form-intro"><?php echo esc_html($form['intro']); ?></p>
        <?php endif; ?>

        <form
            class="landing__form-element"
            data-landing-form="hubspot"
            data-success-message="<?php echo esc_attr($success_message); ?>"
            data-phone-countries="<?php echo esc_attr(implode(',', $phone_countries)); ?>"
            data-phone-default="<?php echo esc_attr($phone_default); ?>"
            novalidate
        >
            <div class="landing__form-grid">
                <!-- Left column -->
                <div class="landing__form-row">
                    <label class="landing__form-label" for="ld-lastname">
                        Name <span class="landing__form-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="ld-lastname"
                        name="lastname"
                        class="landing__form-input"
                        placeholder="Your name"
                        autocomplete="name"
                        required
                    />
                    <p class="landing__form-error" data-error-for="lastname"></p>
                </div>

                <div class="landing__form-row">
                    <label class="landing__form-label" for="ld-company">Company Name</label>
                    <input
                        type="text"
                        id="ld-company"
                        name="company"
                        class="landing__form-input"
                        placeholder="Company name"
                        autocomplete="organization"
                    />
                    <p class="landing__form-error" data-error-for="company"></p>
                </div>

                <div class="landing__form-row">
                    <label class="landing__form-label" for="ld-email">
                        Email <span class="landing__form-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="email"
                        id="ld-email"
                        name="email"
                        class="landing__form-input"
                        placeholder="Youremail@example.com"
                        autocomplete="email"
                        required
                    />
                    <p class="landing__form-error" data-error-for="email"></p>
                </div>

                <div class="landing__form-row">
                    <label class="landing__form-label" for="ld-state">State/ Region</label>
                    <select
                        id="ld-state"
                        name="state_dropdown"
                        class="landing__form-input landing__form-select"
                    >
                        <?php $render_options($state_options, 'Region'); ?>
                    </select>
                    <p class="landing__form-error" data-error-for="state_dropdown"></p>
                </div>

                <div class="landing__form-row">
                    <label class="landing__form-label" for="ld-phone">
                        WhatApp Phone Number <span class="landing__form-required" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="tel"
                        id="ld-phone"
                        name="phone"
                        class="landing__form-input landing__form-phone"
                        placeholder="(+60)1234 5678"
                        autocomplete="tel"
                        required
                    />
                    <p class="landing__form-error" data-error-for="phone"></p>
                </div>

                <div class="landing__form-row">
                    <label class="landing__form-label" for="ld-language">Preferred Language</label>
                    <select
                        id="ld-language"
                        name="hs_language"
                        class="landing__form-input landing__form-select"
                    >
                        <?php $render_options($language_options, 'English'); ?>
                    </select>
                    <p class="landing__form-error" data-error-for="hs_language"></p>
                </div>

                <div class="landing__form-row">
                    <label class="landing__form-label" for="ld-industry">
                        Your Industry <span class="landing__form-required" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="ld-industry"
                        name="your_industry"
                        class="landing__form-input landing__form-select"
                        required
                    >
                        <?php $render_options($industry_options, 'Financial Technology'); ?>
                    </select>
                    <p class="landing__form-error" data-error-for="your_industry"></p>
                </div>

                <!-- Submit cell aligns with the grid -->
                <div class="landing__form-row landing__form-row--submit">
                    <button type="submit" class="landing__form-submit">
                        <?php echo esc_html($form['submit_label'] ?? 'Submit'); ?>
                    </button>
                </div>
            </div>

            <?php // Hidden: campaign default for "Interested Product" (HubSpot prop pos_typetype). ?>
            <input type="hidden" name="pos_typetype" value="<?php echo esc_attr($interested_default); ?>" />

            <?php // Honeypot — must stay empty (real users don't see it; bots fill any input). ?>
            <div class="landing__form-honeypot" aria-hidden="true">
                <label for="ld-website-url">Website (leave blank)</label>
                <input type="text" id="ld-website-url" name="website_url" tabindex="-1" autocomplete="off" />
            </div>

            <p class="landing__form-status" role="status" aria-live="polite"></p>
        </form>
    </div>
</section>
