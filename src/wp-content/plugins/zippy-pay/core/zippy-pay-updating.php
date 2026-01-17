<?php

namespace ZIPPY_Pay\Core;

use stdClass;
defined('ABSPATH') || exit;


class ZIPPY_Pay_Updating
{

	public $plugin_slug;
	public $version;
	public $cache_key;
	public $cache_allowed;


	/**
	 * ZIPPY_Pay_Updating constructor.
	 */

	public function __construct($current_version)
	{

		

		$this->plugin_slug = 'zippy-pay';
		$this->version = $current_version;
		$this->cache_key = 'zippy-key';
		$this->cache_allowed = false;

		add_filter('plugins_api', array($this, 'info'), 20, 3);
		add_filter('site_transient_update_plugins', array($this, 'update'));
		add_action('upgrader_process_complete', array($this, 'purge'), 10, 2);
	}

	public function request()
	{

		$remote = wp_remote_get(
			'https://updateserver.lomago.net/update-info.json'
		);

		if (
			is_wp_error($remote)
			|| 200 !== wp_remote_retrieve_response_code($remote)
			|| empty(wp_remote_retrieve_body($remote))
		) {

			return false;
		}

		set_transient($this->cache_key, $remote, DAY_IN_SECONDS);

		$remote = json_decode(wp_remote_retrieve_body($remote));

		return $remote;
	}


	function info($res, $action, $args)
	{

		// do nothing if you're not getting plugin information right now
		if ('plugin_information' !== $action) {
			return $res;
		}

		// do nothing if it is not our plugin
		if ($this->plugin_slug !== 'zippy-pay.php') {
			return $res;
		}

		// get updates
		$remote = $this->request();

		if (!$remote) {
			return $res;
		}

		$res = new stdClass();

		$res->slug = 'zippy-pay';
		$res->version = $remote->version;

		$res->download_link = $remote->download_url;
		$res->trunk = $remote->download_url;

		return $res;
	}

	public function update($transient)
	{

		if (empty($transient->checked)) {
			return $transient;
		}

		$remote = $this->request();
		if (
			$remote
			&& version_compare($this->version, $remote->version, '<')
		) {
			$res = new stdClass();
			$res->slug = $this->plugin_slug;
			$res->plugin = ZIPPY_PAY_DIR_PATH . 'zippy-pay.php';
			$res->new_version = $remote->version;
			$res->package = $remote->download_url;

			$transient->response[$res->plugin] = $res;
		}


		return $transient;
	}

	public function purge($upgrader, $options)
	{

		if (
			$this->cache_allowed
			&& 'update' === $options['action']
			&& 'plugin' === $options['type']
		) {
			// just clean the cache when new plugin version is installed
			delete_transient($this->cache_key);
		}
	}
}
