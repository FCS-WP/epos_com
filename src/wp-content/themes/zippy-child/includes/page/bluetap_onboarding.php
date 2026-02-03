<?php
function bluetap_onboarding_device_switch_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'desktop' => '[block id="bluetap-onboarding-desktop"]',
        'mobile'  => '[block id="bluetap-onboarding-mobile"]',
    ), $atts);

    if (wp_is_mobile()) {
        return do_shortcode($atts['mobile']);
    }

    return do_shortcode($atts['desktop']);
}
add_shortcode('bluetap_onboarding_device_switch', 'bluetap_onboarding_device_switch_shortcode');


// Bluetap Onboarding  video shortcode

function bluetap_video_modal_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'image' => '',
        'title' => '',
        'video' => '',
        'id'    => uniqid('bt-modal-'),
    ), $atts);

    ob_start();
?>
    <div class="bt-video-wrapper">
        <div class="bt-video-card" data-target="<?php echo esc_attr($atts['id']); ?>">
            <div class="bt-video-bg"
                style="background-image:url('<?php echo esc_url($atts['image']); ?>')">
                  <div class="bt-video-overlay-card"></div>
                <span class="bt-play-btn"></span>
                <div class="bt-video-title"><?php echo esc_html($atts['title']); ?></div>
            </div>
        </div>

        <div id="<?php echo esc_attr($atts['id']); ?>" class="bt-video-modal">
            <div class="bt-video-overlay"></div>
            <div class="bt-video-box">
                <video
                    class="bt-video"
                    playsinline
                    preload="auto"
                    controls
                    muted
                    autoplay>
                    <source src="<?php echo esc_url($atts['video']); ?>" type="video/mp4">
                </video>
                <button class="bt-close">×</button>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('bluetap_video_card', 'bluetap_video_modal_shortcode');
