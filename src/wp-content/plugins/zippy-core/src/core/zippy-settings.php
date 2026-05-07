<?php

/**
 * Settings theme
 *
 * @package Shin
 */

namespace Zippy_Core\Src\Core;

defined('ABSPATH') or die();

class Zippy_Settings
{

	public function __construct()
	{
		//load all class in here
		$this->set_hooks();
	}

	protected function set_hooks()
	{

		// $this->debug_mode();
		//Allow upload svg
		add_filter('upload_mimes', [$this, 'add_file_types_to_uploads']);
		//Disable auto save
		add_action('admin_init', [$this, 'disable_autosave']);
		//Custom structure menu html
		add_filter('nav_menu_css_class', [$this, 'add_additional_class_on_li'], 1, 3);

		add_filter('body_class', [$this, 'shin_add_slug_to_body_class']);

		add_filter('body_class', [$this, 'shin_add_class_to_body']);

		add_action('wp_enqueue_scripts', [$this, 'zippy_core_add_scripts_web']);
	}

	public function debug_mode()
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}

	public function check_health_check_endpoint($url)
	{
		// Make the HTTP request
		$response = wp_remote_get($url);

		// Check for errors
		if (is_wp_error($response)) {
			return array(
				'status' => 'error',
				'message' => $response->get_error_message()
			);
		}

		// Get the response body
		$body = wp_remote_retrieve_body($response);

		// Decode the JSON response
		$data = json_decode($body, true);

		// Return the result
		return $data;
	}

	// Example usage

	public function add_file_types_to_uploads($file_types)
	{
		$new_filetypes        = array();
		$new_filetypes['svg'] = 'image/svg+xml';
		$file_types           = array_merge($file_types, $new_filetypes);

		return $file_types;
	}

	public function disable_autosave()
	{
		wp_deregister_script('autosave');
	}

	public function shin_add_class_to_body($classes)
	{
		$classes['shin'] = 'shin-theme';
		return $classes;
	}


	public function add_additional_class_on_li($classes, $item, $args)
	{
		if (isset($args->add_li_class)) {
			$classes[] = $args->add_li_class;
		}

		return $classes;
	}

	public function shin_add_slug_to_body_class($classes)
	{
		global $post;
		if (is_home()) {
			$key = array_search('blog', $classes);
			if ($key > -1) {
				unset($classes[$key]);
			}
		} elseif (is_page()) {
			$classes[] = sanitize_html_class($post->post_name);
		} elseif (is_singular()) {
			$classes[] = sanitize_html_class($post->post_name);
		}

		return $classes;
	}

	function zippy_core_add_scripts_web()
	{
		// core-web-scripts removed: source index.js is an empty jQuery wrapper that
		// webpack was bundling the entire jQuery library into (86 KB), duplicating
		// the WP core jQuery already loaded on every page.
		// If frontend JS is needed later, re-enqueue here.

		// core-web-styles is checkout-specific (built from _custom_checkout_page.scss).
		// Only enqueue on checkout to avoid shipping it site-wide.
		if (function_exists('is_checkout') && is_checkout()) {
			$css_path = ZIPPY_CORE_DIR_PATH . '/assets/dist/css/web.min.css';
			$version  = file_exists($css_path) ? filemtime($css_path) : '1.0.0';
			wp_enqueue_style('core-web-styles', ZIPPY_CORE_URL . '/assets/dist/css/web.min.css', [], $version);
		}
	}
}
