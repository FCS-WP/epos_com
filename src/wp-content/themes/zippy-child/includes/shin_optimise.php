<?php


add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);
function add_defer_attribute($tag, $handle)
{
	// add script handles to the array below
	$scripts_to_defer = array('main-scripts-js', '');

	foreach ($scripts_to_defer as $defer_script) {
		if ($defer_script === $handle) {
			return str_replace(' src', ' defer src', $tag);
		}
	}
	return $tag;
}

add_filter('script_loader_tag', 'add_async_attribute', 10, 2);
function add_async_attribute($tag, $handle)
{
	// add script handles to the array below
	$scripts_to_async = array('formidable-js', '');

	foreach ($scripts_to_async as $async_script) {
		if ($async_script === $handle) {
			return str_replace(' src', ' async src', $tag);
		}
	}
	return $tag;
}

remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

add_action('wp_enqueue_scripts', 'remove_block_css', 100);

function remove_block_css()
{
	wp_dequeue_style('wp-block-library'); // Wordpress core
	wp_dequeue_style('wp-block-library-theme'); // Wordpress core
	wp_dequeue_style('wc-block-style'); // WooCommerce
	wp_dequeue_style('storefront-gutenberg-blocks'); // Storefront theme
}

add_filter('rest_authentication_errors', 'authentication_rest_api_not_logged_in');

function authentication_rest_api_not_logged_in($errors)
{

	if (is_wp_error($errors)) {
		return $errors;
	}

	if (! is_user_logged_in() || ! current_user_can('administrator')) {
		return new WP_Error(
			'no_rest_api_sorry',
			'REST API not allowed',
			array('status' => 401)
		);
	}

	return $errors;
}
