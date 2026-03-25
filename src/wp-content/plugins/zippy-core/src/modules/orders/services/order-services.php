<?php

namespace Zippy_Core\Orders\Services;

use WC_Order_Item_Product;
use WC_Tax;
use Dompdf\Dompdf;
use Dompdf\Options;
use Zippy_Core\Utils\Zippy_Wc_Calculate_Helper;
use Automattic\WooCommerce\Admin\Overrides\OrderRefund;

class Order_Services
{
    public static function handle_orders_v1($infos)
    {
        $page         = $infos['page'] ?? null;
        $per_page     = $infos['per_page'] ?? null;
        $order_by     = $infos['order_by'] ?? 'date';
        $order_val    = $infos['order_val'] ?? 'DESC';
        $order_status = $infos['order_status'] ?? null;
        $date_from    = $infos['date_from'] ?? null;
        $date_to      = $infos['date_to'] ?? null;
        $customer_id  = $infos['customer_id'] ?? null;

        $args = [
            'limit'   => $per_page,
            'page'    => $page,
            'orderby' => $order_by,
            'order'   => $order_val,
            'return'  => 'objects',
            'type'    => 'shop_order',
        ];

        if (!empty($order_status)) {
            $statuses = array_map('trim', explode(',', $order_status));
            $args['status'] = $statuses;
        }

        if ($date_from && $date_to) {
            $args['date_created'] = $date_from . '...' . $date_to;
        } elseif ($date_from) {
            $args['date_created'] = '>' . $date_from . ' 00:00:00';
        } elseif ($date_to) {
            $args['date_created'] = '<' . $date_to . ' 23:59:59';
        }

        if (!empty($customer_id)) {
            $args['customer_id'] = $customer_id;
        }

        $orders = wc_get_orders($args);

        $data = [];

        foreach ($orders as $order) {
            if ($order instanceof OrderRefund) {
                continue;
            }

            $data[] = self::parse_order_data($order);
        }

        $count_args = [
            'return' => 'ids',
            'limit'  => -1,
        ];

        if (!empty($order_status)) {
            $count_args['status'] = $args['status'];
        }

        if (!empty($args['date_created'])) {
            $count_args['date_created'] = $args['date_created'];
        }

        $total_orders = count(wc_get_orders($count_args));
        $total_pages  = ceil($total_orders / $per_page);

        return array(
            'page'         => $page,
            'per_page'     => $per_page,
            'total_pages'  => $total_pages,
            'total_orders' => $total_orders,
            'orders'       => $data,
        );
    }

    public static function parse_order_data($order)
    {
        $billing  = $order->get_address('billing');
        $shipping = !array_filter($order->get_address('shipping')) ? $billing : $order->get_address('shipping');

        $data = [
            'id'           => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'date_created' => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '',
            'status'       => $order->get_status(),
            'total'        => $order->get_total(),
            'currency'     => $order->get_currency(),
            'payment_method' => [
                'id'    => $order->get_payment_method(),
                'title' => $order->get_payment_method_title(),
            ],
            'billing' => [
                'first_name' => $billing['first_name'],
                'last_name'  => $billing['last_name'],
                'company'    => $billing['company'],
                'email'      => $billing['email'],
                'phone'      => $billing['phone'],
                'address_1'  => $billing['address_1'],
                'address_2'  => $billing['address_2'],
                'city'       => $billing['city'],
                'state'      => $billing['state'],
                'postcode'   => $billing['postcode'],
                'country'    => $billing['country'],
            ],
            'shipping' => [
                'first_name' => $shipping['first_name'],
                'last_name'  => $shipping['last_name'],
                'company'    => $shipping['company'],
                'address_1'  => $shipping['address_1'],
                'address_2'  => $shipping['address_2'],
                'city'       => $shipping['city'],
                'state'      => $shipping['state'],
                'postcode'   => $shipping['postcode'],
                'country'    => $shipping['country'],
            ],
            'items' => array_map(function ($item) {
                return [
                    'product_id' => $item->get_product_id(),
                    'name'       => $item->get_name(),
                    'quantity'   => $item->get_quantity(),
                    'subtotal'   => $item->get_subtotal(),
                    'total'      => $item->get_total(),
                ];
            }, $order->get_items()),
            'source'      => $order->get_meta('_wc_order_attribution_utm_source') ?: 'website',
        ];

        return $data;
    }

    public static function bulk_action_update_order_status(array $data)
    {
        $order_ids = $data['order_ids'] ?? [];
        $status    = $data['status'] ?? '';
        $action    = $data['action'] ?? '';

        $valid_statuses = wc_get_order_statuses();
        if (!array_key_exists($status, $valid_statuses)) {
            return [];
        }

        $updated_orders = [];
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if ($action == 'restore' && $order && $order->get_status() !== 'trash') {
                return [];
            }

            if ($order) {
                $order->update_status($status, 'Order status updated via API', true);
                $updated_orders[] = $order_id;
            }
        }

        return [
            'updated_orders' => $updated_orders,
            'new_status' => $status,
        ];
    }

    /**
     * Export orders
     * @param array $paramInfos
     * @return array{file_base64: string, file_name: string, file_type: string|\WP_Error}
     */
    public static function export_orders(array $paramInfos)
    {
        $date_from = sanitize_text_field($paramInfos['date_from'] ?? '');
        $date_to   = sanitize_text_field($paramInfos['date_to'] ?? '');
        $format    = sanitize_text_field($paramInfos['format'] ?? 'csv');
        $search    = isset($paramInfos['search']) ? trim($paramInfos['search']) : '';

        $args = [
            'limit'   => -1,
            'return'  => 'objects',
        ];

        if ($date_from && $date_to) {
            $args['date_created'] = $date_from . '...' . $date_to;
        } elseif ($date_from) {
            $args['date_created'] = '>' . $date_from . ' 00:00:00';
        } elseif ($date_to) {
            $args['date_created'] = '<' . $date_to . ' 23:59:59';
        }

        if (!empty($search)) {
            if (is_numeric($search)) {
                $args['post__in'] = [absint($search)];
            }
        }

        $orders = wc_get_orders($args);

        if (!empty($search) && !is_numeric($search)) {
            $filtered = [];
            foreach ($orders as $order) {
                if ($order instanceof OrderRefund) continue;
                if (self::order_matches_search($order, $search)) {
                    $filtered[] = $order;
                }
            }
            $orders = $filtered;
        }

        if (empty($orders)) {
            return new \WP_Error('no_orders', 'No orders found for the selected range/search.');
        }

        $order_rows = [];
        foreach ($orders as $order) {
            if ($order instanceof OrderRefund) {
                continue;
            }
            $country = $order->get_billing_country();
            $state   = $order->get_billing_state();

            $states = WC()->countries->get_states($country);
            $state_name = $states[$state] ?? $state;

            $order_rows[] = [
                'order_id'       => $order->get_id(),
                'phone'          => $order->get_billing_phone(),
                'name'      => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'email'       => $order->get_billing_email(),
                'company'           => $order->get_billing_company(),
                'street'        => trim(
                    $order->get_billing_address_1() . ' ' . $order->get_billing_address_2()
                ),
                'city'          => $order->get_billing_city(),
                'postcode'     => $order->get_billing_postcode(),
                'state'           => $state_name,
                'items'         => self::get_items_data($order),
                'status'         => wc_get_order_status_name($order->get_status()),
                'total' => html_entity_decode(wp_strip_all_tags(wc_price($order->get_total()))),
                'payment_method' => $order->get_payment_method_title(),
                'order_note' => $order->get_customer_note(),
                'date_created'   => $order->get_date_created()->date_i18n('Y-m-d H:i:s'),
                'source'      => $order->get_meta('_wc_order_attribution_utm_source') ?: 'website',
            ];
        }

        // Generate file content (do not save on server)
        $content = '';
        if ($format === 'csv') {
            $content = self::generate_csv($order_rows);
        } elseif ($format === 'pdf') {
            $content = self::generate_pdf($order_rows);
        } else {
            return new \WP_Error('invalid_format', 'Invalid export format.');
        }

        $file_base64 = base64_encode($content);
        $file_name = 'orders_export_' . date('Y-m-d_H-i-s') . '.' . $format;

        return [
            'file_base64' => $file_base64,
            'file_name'   => $file_name,
            'file_type'   => $format,
        ];
    }


    /**
     * Create CSV – return content string
     */
    private static function generate_csv($rows)
    {
        $handle = fopen('php://memory', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($handle, [
            'Name',
            'Phone',
            'Email',
            'Company',
            'Street',
            'City',
            'Postcode',
            'State',
            'Order ID',
            "Products",
            "Notes",
            'Status',
            'Total',
            'Date Created',
            'Source',
        ]);

        foreach ($rows as $r) {
            fputcsv($handle, [
                $r['name'],
                $r['phone'],
                $r['email'],
                $r['company'] ?? '',
                $r['street'],
                $r['city'],
                $r['postcode'],
                $r['state'] ?? '',
                $r['order_id'],
                $r['items'],
                $r['order_note'],
                $r['status'],
                $r['total'],
                $r['date_created'],
                $r['source'],
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        return $content;
    }

    private static function get_items_data($order)
    {
        $items = [];

        foreach ($order->get_items() as $item) {
            if ($item instanceof WC_Order_Item_Product) {
                $items[] = sprintf(
                    '%s (x%d) - %s',
                    $item->get_name(),
                    $item->get_quantity(),
                    html_entity_decode(wp_strip_all_tags(wc_price($item->get_total())))
                );
            }
        }

        return implode("\n", $items);
    }


    /**
     * Create PDF – return content string
     */
    private static function generate_pdf($rows)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $html = '
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            h2 { text-align:center; margin-bottom: 10px; }
            table { width:100%; border-collapse: collapse; }
            th, td { border:1px solid #ccc; padding:8px; text-align:left; }
            th { background-color:#f2f2f2; }
        </style>
        <h2>Orders Export</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Phone</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>State</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Date Created</th>
                    <th>Source</th>
                    
                </tr>
            </thead>
            <tbody>';

        foreach ($rows as $r) {
            $html .= '<tr>
                <td>' . esc_html($r['order_id']) . '</td>
                <td>' . esc_html($r['phone']) . '</td>
                <td>' . esc_html($r['name']) . '</td>
                <td>' . esc_html($r['email']) . '</td>
                <td>' . esc_html($r['company']) . '</td>
                <td>' . esc_html($r['state']) . '</td>
                <td>' . esc_html($r['status']) . '</td>
                <td>' . esc_html($r['total']) . '</td>
                <td>' . esc_html($r['date_created']) . '</td>
                <td>' . esc_html($r['source']) . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }


    public static function move_orders_to_trash(array $order_ids)
    {
        $trashed = [];
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);

            if ($order) {
                $order->delete(false);
                $trashed[] = $order_id;
            }
        }

        return $trashed;
    }

    public static function search_orders($infos) {}


    public static function custom_send_wc_email($order_id,  $email_type)
    {
        if (! function_exists('wc_get_order')) {
            return new WP_Error('woocommerce_not_loaded', 'WooCommerce is not active.');
        }
        $order = wc_get_order($order_id);

        $email_templates = [
            'new_order'        => 'emails/admin-new-order.php',
            'cancelled_order'  => 'emails/admin-cancelled-order.php',
            'failed_order'     => 'emails/admin-failed-order.php',
            'customer_invoice' => 'emails/customer-invoice.php',
            'completed_order'  => 'emails/customer-completed-order.php',
            'processing_order' => 'emails/customer-processing-order.php',
        ];

        if (! isset($email_templates[$email_type])) {
            return new WP_Error('invalid_email_type', "Email type '$email_type' not supported.");
        }
        $template_file = $email_templates[$email_type];


        // Load WooCommerce mailer
        $mailer = WC()->mailer();

        // Start buffering the template output
        ob_start();

        wc_get_template(
            $template_file,
            [
                'order'         => $order,
                'email_heading' => ucfirst(str_replace('_', ' ', $email_type)),
                'sent_to_admin' => in_array($email_type, ['new_order', 'cancelled_order', 'failed_order']),
                'plain_text'    => false,
                'email'         => $mailer,
            ]
        );

        $message = ob_get_clean();

        // Get subject based on type
        $subjects = [
            'new_order'        => sprintf(__('New order #%s', 'woocommerce'), $order->get_order_number()),
            'cancelled_order'  => sprintf(__('Cancelled order #%s', 'woocommerce'), $order->get_order_number()),
            'failed_order'     => sprintf(__('Failed order #%s', 'woocommerce'), $order->get_order_number()),
            'customer_invoice' => sprintf(__('Invoice for order #%s', 'woocommerce'), $order->get_order_number()),
            'completed_order'  => sprintf(__('Your order #%s is complete', 'woocommerce'), $order->get_order_number()),
            'processing_order' => sprintf(__('Your order #%s is being processed', 'woocommerce'), $order->get_order_number()),
        ];

        $subject = $subjects[$email_type] ?? 'Order Update';

        // Set recipient based on email type
        $to = in_array($email_type, ['new_order', 'cancelled_order', 'failed_order'])
            ? 'dev@zippy.sg'
            : $order->get_billing_email();

        // Wrap email with WooCommerce header/footer
        $wrapped_message = $mailer->wrap_message($subject, $message);

        // Get headers from WooCommerce settings
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . wp_specialchars_decode(get_option('woocommerce_email_from_name'), ENT_QUOTES) .
                ' <' . get_option('woocommerce_email_from_address') . '>',
        ];

        $sent = wp_mail($to, $subject, $wrapped_message, $headers);
        return $sent;
    }

    public static function get_summary_orders($filters)
    {
        global $wpdb;

        $start_date = !empty($filters['date_from']) ? $filters['date_from'] : null;
        $end_date   = !empty($filters['date_to']) ? $filters['date_to'] : null;

        $table = $wpdb->prefix . 'wc_orders';
        $where = "WHERE 1=1";

        $where .= " AND status NOT IN ('trash', 'auto-draft')";

        if ($start_date) {
            $end_date = $end_date ?: gmdate('Y-m-d');
            $where .= $wpdb->prepare(
                " AND date_created_gmt BETWEEN %s AND %s",
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            );
        }

        $total_orders = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where");

        $completed_orders = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where AND status = 'wc-completed'");
        $cancelled_orders = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where AND status = 'wc-cancelled'");
        $pending_orders   = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where AND status = 'wc-pending'");

        $results = [
            'total_orders'     => $total_orders,
            'completed_orders' => $completed_orders,
            'cancelled_orders' => $cancelled_orders,
            'pending_orders'   => $pending_orders,
        ];

        return [
            'results' => $results,
        ];
    }

    public static function search_customers($q)
    {
        $customers = get_users([
            'role'           => 'customer',
            'search'         => '*' . esc_attr($q) . '*',
            'search_columns' => ['user_email', 'display_name', 'user_login'],
            'orderby'        => 'user_registered',
            'order'          => 'DESC',
        ]);

        $results = [];

        foreach ($customers as $customer) {
            $results[] = [
                'id'    => $customer->ID,
                'label' =>  $customer->display_name . ' (' . $customer->user_email . ')',
            ];
        }

        return [
            'results' => $results,
        ];
    }

    public static function handle_orders($infos)
    {
        $page         = $infos['page'] ?? null;
        $per_page     = $infos['per_page'] ?? null;
        $order_by     = $infos['order_by'] ?? 'date';
        $order_val    = $infos['order_val'] ?? 'DESC';
        $order_status = $infos['order_status'] ?? null;
        $date_from    = $infos['date_from'] ?? null;
        $date_to      = $infos['date_to'] ?? null;
        $customer_id  = $infos['customer_id'] ?? null;
        $search       = isset($infos['search']) ? trim($infos['search']) : null;

        $base_args = [
            'orderby' => $order_by,
            'order'   => $order_val,
            'type'    => 'shop_order',
            'return'  => 'objects',
        ];

        if (!empty($order_status)) {
            $base_args['status'] = array_map('trim', explode(',', $order_status));
        }

        if ($date_from && $date_to) {
            $base_args['date_created'] = "{$date_from}...{$date_to}";
        } elseif ($date_from) {
            $base_args['date_created'] = ">{$date_from} 00:00:00";
        } elseif ($date_to) {
            $base_args['date_created'] = "<{$date_to} 23:59:59";
        }

        if (!empty($customer_id)) {
            $base_args['customer_id'] = $customer_id;
        }

        if (!empty($search)) {
            // Numeric = direct order ID lookup, no need to scan everything
            if (is_numeric($search)) {
                $base_args['post__in'] = [absint($search)];
            } else {
                // Resolve matched WP user IDs from search
                $matched_customer_ids = self::find_customer_ids_by_search($search);

                if (!empty($matched_customer_ids)) {
                    if (!empty($customer_id)) {
                        // Intersect explicit customer_id with search results
                        if (!in_array((int) $customer_id, $matched_customer_ids, true)) {
                            return self::empty_result($page, $per_page);
                        }
                    } else {
                        $base_args['customer_id'] = $matched_customer_ids;
                    }
                }

                // Always fetch all (no limit) and filter in PHP using decrypted billing data
                // This is the only reliable approach when order meta is encrypted
                $base_args['limit'] = -1;
                unset($base_args['page']);
            }
        }

        // -------------------------------------------------------------------------
        // Fetch orders
        // -------------------------------------------------------------------------
        $use_php_search = !empty($search) && !is_numeric($search);

        if ($use_php_search) {
            // Fetch all matched orders, filter by billing fields in PHP
            $all_orders = wc_get_orders($base_args);
            $filtered   = [];

            foreach ($all_orders as $order) {
                if ($order instanceof OrderRefund) {
                    continue;
                }

                if (self::order_matches_search($order, $search)) {
                    $filtered[] = $order;
                }
            }

            $total_orders = count($filtered);
            $total_pages  = $per_page > 0 ? (int) ceil($total_orders / $per_page) : 1;

            // Manual pagination on filtered results
            $offset = $per_page > 0 ? ($page - 1) * $per_page : 0;
            $paged  = array_slice($filtered, $offset, $per_page ?: null);

            return [
                'page'         => $page,
                'per_page'     => $per_page,
                'total_pages'  => $total_pages,
                'total_orders' => $total_orders,
                'orders'       => array_map([self::class, 'parse_order_data'], $paged),
            ];
        }

        // Normal paginated path (no search or numeric order ID search)
        $orders = wc_get_orders(array_merge($base_args, [
            'limit' => $per_page,
            'page'  => $page,
        ]));

        $data = [];
        foreach ($orders as $order) {
            if (!($order instanceof OrderRefund)) {
                $data[] = self::parse_order_data($order);
            }
        }

        $total_orders = count(wc_get_orders(array_merge($base_args, [
            'limit'  => -1,
            'return' => 'ids',
        ])));

        return [
            'page'         => $page,
            'per_page'     => $per_page,
            'total_pages'  => $per_page > 0 ? (int) ceil($total_orders / $per_page) : 1,
            'total_orders' => $total_orders,
            'orders'       => $data,
        ];
    }

    /**
     * Check if an order matches the search string against decrypted billing data.
     * WooCommerce decrypts values automatically via get_address() / getters.
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
     * Does NOT touch order meta — used to pre-filter wc_get_orders by customer_id.
     */
    private static function find_customer_ids_by_search(string $search): array
    {
        $ids = [];

        foreach (['email', 'login'] as $field) {
            $user = get_user_by($field, $search);
            if ($user) {
                $ids[] = (int) $user->ID;
            }
        }

        $user_query = new \WP_User_Query([
            'search'         => '*' . esc_attr($search) . '*',
            'search_columns' => ['user_login', 'user_email', 'display_name'],
            'fields'         => 'ID',
            'number'         => 50,
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => 'first_name',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ],
                [
                    'key'     => 'last_name',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        foreach ($user_query->get_results() as $uid) {
            $ids[] = (int) $uid;
        }

        return array_values(array_unique($ids));
    }

    private static function empty_result($page, $per_page): array
    {
        return [
            'page'         => $page,
            'per_page'     => $per_page,
            'total_pages'  => 0,
            'total_orders' => 0,
            'orders'       => [],
        ];
    }
}
