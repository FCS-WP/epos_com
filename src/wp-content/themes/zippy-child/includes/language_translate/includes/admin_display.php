<?php
// Display multi language column
add_filter('manage_page_posts_columns', function ($columns) {
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'title') {
            $new['multi_lang'] = 'Multi Languages';
        }
    }
    return $new;
});

add_action('manage_page_posts_custom_column', function ($column, $post_id) {
    if ($column !== 'multi_lang') return;
    $enabled = get_post_meta($post_id, 'multi_languages_page', true);
    if (!$enabled) {
        echo '<span style="color:#999;">-</span>';
        return;
    }
    $slug = get_post_field('post_name', $post_id);
    $langs = Lang::supported();
    if (empty($langs)) {
        echo esc_html($slug);
        return;
    }
    if (in_array($slug, $langs, true)) {
        echo '<span style="color:#2271b1;">' . esc_html(strtoupper($slug)) . '</span>';
    } else {
        echo '<span style="color:#2271b1;">' . esc_html(implode(', ', array_map('strtoupper', $langs))) . '</span>';
    }
}, 10, 2);


// Add filter for multi language pages
add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'page') return;
    $selected = $_GET['multi_lang_filter'] ?? '';
    ?>
    <select name="multi_lang_filter">
        <option value="">All Languages</option>
        <option value="1" <?php selected($selected, '1'); ?>>
            Multi Language
        </option>
    </select>
    <?php
});

add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('post_type') !== 'page') return;
    if (!isset($_GET['multi_lang_filter']) || $_GET['multi_lang_filter'] === '') return;
    $value = $_GET['multi_lang_filter'];
    if ($value === '1') {
        $query->set('meta_query', [
            [
                'key'     => 'multi_languages_page',
                'compare' => 'EXISTS',
            ]
        ]);
    }
});