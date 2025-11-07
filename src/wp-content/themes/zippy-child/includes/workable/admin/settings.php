<?php
if (!defined('ABSPATH')) exit;

// Add menu item
add_action('admin_menu', function () {
    add_menu_page(
        'Workable',
        'Workable',
        'manage_options',
        'workable',
        'render_workable_admin_page',
        'dashicons-admin-generic',
        65
    );
});

// Register setting
add_action('admin_init', function () {
    register_setting('workable_settings_group', WORKABLE_CLIENT_KEY);
});

// Render admin page
function render_workable_admin_page() { ?>
    <div class="wrap">
        <h1>Workable Configuration</h1>
        <form method="post" action="options.php">
            <?php settings_fields('workable_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="<?php echo WORKABLE_CLIENT_KEY; ?>">Workable API Key</label></th>
                    <td>
                        <input
                            type="text"
                            id="<?php echo WORKABLE_CLIENT_KEY; ?>"
                            name="<?php echo WORKABLE_CLIENT_KEY; ?>"
                            value="<?php echo esc_attr(get_option(WORKABLE_CLIENT_KEY, '')); ?>"
                            size="50"
                            placeholder="Enter your Workable API key here" />
                    </td>
                </tr>
            </table>
            <?php submit_button('Save API Key'); ?>
        </form>
    </div>
<?php }
