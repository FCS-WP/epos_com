<?php

namespace Zippy_Core\Orders\Services;

use Zippy_Core\Core_Settings;

class Order_Setting_Services
{
    /**
     * init core configs
     */

    public static function init_invoices_option()
    {
        $init_configs = [
            'invoice-logo' => [
                "value" => esc_url(self::get_custom_logo_url()),
                "type" => 'link',
                "position" => 'logo'
            ],
            'store-name' => [
                "value" => get_bloginfo('name'),
                "type" => 'text',
                "position" => 'logo'
            ],
            'company-address' => [
                "value" => "Example Address",
                "type" => 'text',
                "position" => 'header'
            ],
            'company-phone' => [
                "value" => "65 6665 5566",
                "type" => 'text',
                "position" => 'footer'
            ],
        ];

        $option_key = Core_Settings::OPTIONS_KEY_ORDER_INVOICES_CONFIGS;
        $existing = get_option($option_key);

        if (! is_array($existing)) {
            add_option($option_key, $init_configs);
            $existing = $init_configs;
        }
        return $existing;
    }

    public static function get_custom_logo_url()
    {
        $logo_id = get_theme_mod('custom_logo');
        if (!$logo_id) return '';
        $src = wp_get_attachment_image_src($logo_id, 'full');
        return is_array($src) ? $src[0] : '';
    }

    public static function init_order_detail_option()
    {
        $configs = [
            'custom_order_items'      => 'no',
        ];

        $option_key = Core_Settings::OPTIONS_KEY_ORDER_DETAILS_CONFIGS;
        $existing = get_option($option_key);

        if (! is_array($existing)) {
            add_option($option_key, $configs);
            $existing = $configs;
        }

        return $existing;
    }

    /**
     * update new invoices settings
     */

    public static function update_invoices_options($data)
    {
        $option_key = Core_Settings::OPTIONS_KEY_ORDER_INVOICES_CONFIGS;
        $new_config = [];

        foreach ($data as $new_item) {
            $key = $new_item['key'];
            $type = $new_item['data']['type'];
            $position =  $new_item['data']['position'];
            $value = $type == 'link' ? esc_url($new_item['data']['value']) : $new_item['data']['value'];

            if ($key == 'invoice-logo' && empty($value)) {
                $value = esc_url(self::get_custom_logo_url());
            }

            $new_config[$key] = [
                'type' => $type,
                'value' => $value,
                'position' => $position
            ];
        }

        update_option($option_key, $new_config);
        return $new_config;
    }
}
