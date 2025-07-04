<?php // [ux_slider]
function shortcode_ux_slider($atts, $content=null) {

    extract( shortcode_atts( array(
        '_id' => 'slider-'.rand(),
        'timer' => '6000',
        'bullets' => 'true',
        'visibility' => '',
        'class' => '',
        'type' => 'slide',
        'bullet_style' => '',
        'auto_slide' => 'true',
        'auto_height' => 'true',
        'bg_color' => '',
        'slide_align' => 'center',
        'style' => 'normal',
        'slide_width' => '',
        'slide_width__md' => null,
        'slide_width__sm' => null,
        'arrows' => 'true',
        'pause_hover' => 'true',
        'hide_nav' => '',
        'nav_style' => 'circle',
        'nav_color' => 'light',
        'nav_size' => 'large',
        'nav_pos' => '',
        'infinitive' => 'true',
        'freescroll' => 'false',
        'parallax' => '0',
        'margin' => '',
        'margin__md' => '',
        'margin__sm' => '',
        'columns' => '1',
        'height' => '',
        'rtl' => 'false',
        'draggable' => 'true',
        'friction' => '0.6',
        'selectedattraction' => '0.1',
        'threshold' => '10',
        'groupcells' => '',

        // Derpicated
        'mobile' => 'true',

    ), $atts ) );

    // Stop if visibility is hidden
    if($visibility == 'hidden') return;
    if($mobile !==  'true' && !$visibility) {$visibility = 'hide-for-small';}

    ob_start();

    $wrapper_classes = array('slider-wrapper', 'relative');
    if( $class ) $wrapper_classes[] = $class;
    if( $visibility ) $wrapper_classes[] = $visibility;
    $wrapper_classes = implode(" ", $wrapper_classes);

    $classes = array('slider');

    if ($type == 'fade') $classes[] = 'slider-type-'.$type;

    // Bullet style
    if($bullet_style) $classes[] = 'slider-nav-dots-'.$bullet_style;

    // Nav style
    if($nav_style) $classes[] = 'slider-nav-'.$nav_style;

    // Nav size
    if($nav_size) $classes[] = 'slider-nav-'.$nav_size;

    // Nav Color
    if($nav_color) $classes[] = 'slider-nav-'.$nav_color;

    // Nav Position
    if($nav_pos) $classes[] = 'slider-nav-'.$nav_pos;

    // Add timer
    if($auto_slide == 'true') $auto_slide = $timer;

    // Add Slider style
    if($style) $classes[] = 'slider-style-'.$style;

    // Always show Nav if set
    if($hide_nav ==  'true') {$classes[] = 'slider-show-nav';}

    // Slider Nav visebility
    $is_arrows = 'true';
    $is_bullets = 'true';

    if($arrows == 'false') $is_arrows = 'false';
    if($bullets == 'false') $is_bullets = 'false';

    if(is_rtl()) $rtl = 'true';

    $classes = implode(" ", $classes);

    // Inline CSS.
	$css_args = array(
		'bg_color' => array(
			'attribute' => 'background-color',
			'value'     => $bg_color,
		),
	);

	$args = array(
		'margin'      => array(
			'selector' => '',
			'property' => 'margin-bottom',
		),
		'slide_width' => array(
			'selector'  => '.flickity-slider > *',
			'property'  => 'max-width',
			'important' => true,
		),
	);
?>
<div class="<?php echo esc_attr( $wrapper_classes ); ?>" id="<?php echo esc_attr( $_id ); ?>" <?php echo get_shortcode_inline_css($css_args); ?>>
    <div class="<?php echo esc_attr( $classes ); ?>"
        data-flickity-options='{
            "cellAlign": "<?php echo esc_attr( $slide_align ); ?>",
            "imagesLoaded": true,
            "lazyLoad": 1,
            "freeScroll": <?php echo esc_attr( $freescroll ); ?>,
            "wrapAround": <?php echo esc_attr( $infinitive ); ?>,
            "autoPlay": <?php echo esc_attr( $auto_slide );?>,
            "pauseAutoPlayOnHover" : <?php echo esc_attr( $pause_hover ); ?>,
            "prevNextButtons": <?php echo esc_attr( $is_arrows ); ?>,
            "contain" : true,
            "adaptiveHeight" : <?php echo esc_attr( $auto_height ); ?>,
            "dragThreshold" : <?php echo esc_attr( $threshold ); ?>,
            "percentPosition": true,
            "pageDots": <?php echo esc_attr( $is_bullets ); ?>,
            "rightToLeft": <?php echo esc_attr( $rtl ); ?>,
            "draggable": <?php echo esc_attr( $draggable ); ?>,
            "selectedAttraction": <?php echo esc_attr( $selectedattraction ); ?>,
            "parallax" : <?php echo esc_attr( $parallax ); ?>,
            "friction": <?php echo esc_attr( $friction ); ?>,
            "groupCells": <?php echo is_numeric($groupcells) ? $groupcells : '"' . esc_attr($groupcells) . '"'; ?>
        }'
        >
        <?php echo do_shortcode( $content ); ?>
     </div>

     <div class="loading-spin dark large centered"></div>

	<?php echo ux_builder_element_style_tag( $_id, $args, $atts ); ?>
</div>

<?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
add_shortcode("ux_slider", "shortcode_ux_slider");
