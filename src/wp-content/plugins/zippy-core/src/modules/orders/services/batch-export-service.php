<?php
/**
 * Batch_Export_Service: Handles high-performance, low-memory batch export of WooCommerce orders to CSV.
 * Focuses on memory efficiency by processing orders in chunks and appending to disk.
 */

namespace Zippy_Core\Src\Modules\Orders\Services;

defined('ABSPATH') or die();

use Automattic\WooCommerce\Admin\Overrides\OrderRefund;
use WC_Order_Item_Product;

class Batch_Export_Service
{
    /**
     * Start the export process: calculate total record count and generate a unique export ID.
     */
    public static function start_export($params)
    {
        $filter      = $params['filter'] ?? [];
        $search      = isset($filter['search']) ? trim($filter['search']) : null;
        $customer_id = $filter['customer_id'] ?? null;
        
        $base_args = [
            'limit'   => -1,
            'return'  => 'ids',
            'status'  => $filter['order_status'] ?? null,
            'type'    => 'shop_order',
            'orderby' => 'ID',
            'order'   => 'ASC',
        ];
        
        if (!empty($filter['date_from']) && !empty($filter['date_to'])) {
            $base_args['date_created'] = $filter['date_from'] . '...' . $filter['date_to'];
        } elseif (!empty($filter['date_from'])) {
            $base_args['date_created'] = '>' . $filter['date_from'] . ' 00:00:00';
        } elseif (!empty($filter['date_to'])) {
            $base_args['date_created'] = '<' . $filter['date_to'] . ' 23:59:59';
        }

        if (!empty($customer_id)) {
            $base_args['customer_id'] = $customer_id;
        }

        // Apply Search Logic
        if (!empty($search)) {
            if (is_numeric($search)) {
                $base_args['post__in'] = [absint($search)];
                $order_ids = wc_get_orders($base_args);
            } else {
                // String search: Resolve customer IDs matching the search string
                $matched_user_ids = self::find_customer_ids_by_search($search);
                
                // If we also have an explicit customer_id filter, we must intersect
                if (!empty($customer_id)) {
                    if (!in_array((int) $customer_id, $matched_user_ids, true)) {
                        // The searched user is not the same as filtered user, result would be empty
                        return new \WP_Error('no_orders', 'No orders found matching both customer filter and search.');
                    }
                } else if (!empty($matched_user_ids)) {
                    // Temporarily set customer_id to narrow down the query
                    $base_args['customer_id'] = $matched_user_ids;
                }

                // Fetch orders matching status/date/customer, then filter by billing in PHP
                $candidate_ids = wc_get_orders($base_args);
                $matched_ids = [];
                foreach ($candidate_ids as $order_id) {
                    $order = wc_get_order($order_id);
                    if ($order && self::order_matches_search($order, $search)) {
                        $matched_ids[] = $order_id;
                    }
                }
                $order_ids = $matched_ids;
            }
        } else {
            // No search, just status/date/customer
            $order_ids = wc_get_orders($base_args);
        }

        $total_items = count($order_ids);
        if ($total_items === 0) {
            return new \WP_Error('no_orders', 'No orders found for the selected range/search.');
        }

        $export_id = uniqid('export_');

        // Cache the matched IDs to a temp file for process_chunk to consume efficiently
        $upload_dir = wp_upload_dir();
        $zippy_dir = $upload_dir['basedir'] . '/zippy-exports';
        if (!is_dir($zippy_dir)) wp_mkdir_p($zippy_dir);
        
        file_put_contents($zippy_dir . "/ids_{$export_id}.json", json_encode($order_ids));

        return [
            'total_items' => $total_items,
            'export_id'   => $export_id,
            'chunk_size'  => 100,
            'format'      => 'csv'
        ];
    }

    /**
     * Process a single chunk: Fetch orders and APPEND them to the temporary CSV file.
     */
    public static function process_chunk($params)
    {
        $export_id = $params['export_id'] ?? '';
        $offset = isset($params['offset']) ? intval($params['offset']) : 0;
        $limit = isset($params['limit']) ? intval($params['limit']) : 100;
        $filter = $params['filter'] ?? [];

        if (empty($export_id)) {
            return new \WP_Error('invalid_id', 'Invalid export ID.');
        }

        $order_args = [
            'limit'   => $limit,
            'return'  => 'objects',
            'type'    => 'shop_order',
            'orderby' => 'ID',
            'order'   => 'ASC',
        ];

        $upload_dir = wp_upload_dir();
        $zippy_dir = $upload_dir['basedir'] . '/zippy-exports';
        $ids_file = $zippy_dir . "/ids_{$export_id}.json";

        if (file_exists($ids_file)) {
            $all_ids = json_decode(file_get_contents($ids_file), true);
            $chunk_ids = array_slice($all_ids, $offset, $limit);
            if (empty($chunk_ids)) {
                $orders = [];
            } else {
                $order_args['post__in'] = $chunk_ids;
                $order_args['limit'] = -1;
                $orders = wc_get_orders($order_args);
            }
        } else {
            // Fallback to normal query if cache missing (should not happen if start_export worked)
            $order_args['offset'] = $offset;
            $order_args['status'] = $filter['order_status'] ?? null;
            
            if (!empty($filter['date_from']) && !empty($filter['date_to'])) {
                $order_args['date_created'] = $filter['date_from'] . '...' . $filter['date_to'];
            } elseif (!empty($filter['date_from'])) {
                $order_args['date_created'] = '>' . $filter['date_from'] . ' 00:00:00';
            } elseif (!empty($filter['date_to'])) {
                $order_args['date_created'] = '<' . $filter['date_to'] . ' 23:59:59';
            }

            $orders = wc_get_orders($order_args);
        }

        // File preparation
        $upload_dir = wp_upload_dir();
        $zippy_dir = $upload_dir['basedir'] . '/zippy-exports';
        
        if (!is_dir($zippy_dir)) {
            if (!wp_mkdir_p($zippy_dir)) {
                return new \WP_Error('mkdir_failed', 'Unable to create export directory.');
            }
        }
        
        if (!is_writable($zippy_dir)) {
            return new \WP_Error('dir_unwritable', 'Export directory is not writable.');
        }

        $file_name = "orders_{$export_id}.csv";
        $file_path = $zippy_dir . '/' . $file_name;
        
        $mode = ($offset == 0) ? 'w' : 'a';
        $f_handle = fopen($file_path, $mode);

        if (!$f_handle) {
            return new \WP_Error('file_unwritable', 'Unable to open file for writing.');
        }

        if ($offset == 0) {
            fwrite($f_handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($f_handle, [
                'Name', 'Phone', 'Email', 'Company', 'Street', 'City', 'Postcode', 'State',
                'Order ID', 'Products', 'Notes', 'Status', 'Total', 'Date Created', 'Source',
            ]);
        }

        foreach ($orders as $order) {
            if ($order instanceof OrderRefund) {
                continue;
            }

            $country = $order->get_billing_country();
            $state   = $order->get_billing_state();
            $states  = WC()->countries->get_states($country);
            $state_name = $states[$state] ?? $state;

            $items_data = self::get_items_data($order);
            $total_formatted = html_entity_decode(wp_strip_all_tags(wc_price($order->get_total())));
            $date_created = $order->get_date_created() ? $order->get_date_created()->date_i18n('Y-m-d H:i:s') : '';
            $source = $order->get_meta('_wc_order_attribution_utm_source') ?: 'website';

            fputcsv($f_handle, [
                $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                $order->get_billing_phone(),
                $order->get_billing_email(),
                $order->get_billing_company(),
                trim($order->get_billing_address_1() . ' ' . $order->get_billing_address_2()),
                $order->get_billing_city(),
                $order->get_billing_postcode(),
                $state_name,
                $order->get_id(),
                $items_data,
                $order->get_customer_note(),
                wc_get_order_status_name($order->get_status()),
                $total_formatted,
                $date_created,
                $source,
            ]);
        }

        fclose($f_handle);

        return [
            'processed' => count($orders),
            'offset'    => $offset + count($orders)
        ];
    }

    /**
     * Finalize the export process.
     */
    public static function finalize_export($export_id)
    {
        $upload_dir = wp_upload_dir();
        $zippy_dir = $upload_dir['basedir'] . '/zippy-exports';
        $file_name = "orders_{$export_id}.csv";
        $file_path = $zippy_dir . '/' . $file_name;
        
        // Cleanup temp IDs file
        $ids_file = $zippy_dir . "/ids_{$export_id}.json";
        if (file_exists($ids_file)) {
            unlink($ids_file);
        }

        if (file_exists($file_path)) {
            $file_url = $upload_dir['baseurl'] . '/zippy-exports/' . $file_name;
            return ['file_url' => $file_url, 'format' => 'csv'];
        } 

        return new \WP_Error('file_not_found', 'Export file missing.');
    }

    /**
     * Helper to get order items in a comma-separated format.
     */
    private static function get_items_data($order)
    {
        $items = [];
        foreach ($order->get_items() as $item) {
            if ($item instanceof WC_Order_Item_Product) {
                $items[] = sprintf(
                    "%s (x%d) - %s",
                    $item->get_name(),
                    $item->get_quantity(),
                    html_entity_decode(wp_strip_all_tags(wc_price($item->get_total())))
                );
            }
        }
        return implode("; ", $items);
    }

    /**
     * Check if an order matches the search string against billing data.
     */
    private static function order_matches_search($order, string $search): bool
    {
        $search = strtolower($search);
        $billing = $order->get_address('billing');

        $haystack = strtolower(implode(' ', array_filter([
            $billing['first_name'] ?? '',
            $billing['last_name']  ?? '',
            $billing['email']      ?? '',
            $billing['phone']      ?? '',
            $billing['company']    ?? '',
        ])));

        return strpos($haystack, $search) !== false;
    }

    /**
     * Find WP user IDs matching a search string (registered users only).
     * Replicated from Order_Services.
     */
    private static function find_customer_ids_by_search(string $search): array
    {
        $ids = [];
        foreach (['email', 'login'] as $field) {
            $user = get_user_by($field, $search);
            if ($user) $ids[] = (int) $user->ID;
        }

        $user_query = new \WP_User_Query([
            'search'         => '*' . esc_attr($search) . '*',
            'search_columns' => ['user_login', 'user_email', 'display_name'],
            'fields'         => 'ID',
            'number'         => 100,
            'meta_query'     => [
                'relation' => 'OR',
                [['key' => 'first_name', 'value' => $search, 'compare' => 'LIKE']],
                [['key' => 'last_name',  'value' => $search, 'compare' => 'LIKE']],
            ],
        ]);

        foreach ($user_query->get_results() as $uid) {
            $ids[] = (int) $uid;
        }

        return array_values(array_unique($ids));
    }
}
