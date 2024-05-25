<?php

use JetBrains\PhpStorm\NoReturn;

class EasyStateAPIPlugin
{
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_easystate_api_call', [$this, 'handle_form_submission']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'EasyState API Plugin',
            'EasyState API',
            'manage_options',
            'easystate-api-plugin',
            [$this, 'create_admin_page'],
            'dashicons-admin-generic',
            90
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>EasyState API Plugin</h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php
                wp_nonce_field('easystate_api_call_nonce', 'easystate_api_call_nonce_field');
                ?>
                <input type="hidden" name="action" value="easystate_api_call">
                <p>
                    <input type="submit" class="button button-primary" value="Fetch Data">
                </p>
            </form>
        </div>
        <?php
    }

    #[NoReturn] public function handle_form_submission() {
        // Check if the current user has permission to manage options
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        // Verify the nonce field for security
        if (!isset($_POST['easystate_api_call_nonce_field']) || !wp_verify_nonce($_POST['easystate_api_call_nonce_field'], 'easystate_api_call_nonce')) {
            wp_die(__('Nonce verification failed.'));
        }

        // Your logic to handle the API call or other processing
        $properties_manager = new EasyStatePropertiesManager();
        $properties_manager->get_properties();
        $properties_manager->extract_properties();


        // Redirect back to the admin page with a success message
        wp_redirect(add_query_arg('message', 'data_fetched', admin_url('admin.php?page=easystate-api-plugin')));
        exit;
    }

    public function plugin_activation()
    {
        $activation = new EasyStateActivation();
        $activation->activate();
    }

    public function plugin_deactivation()
    {
        $deactivation = new EasyStateDeactivation();
        $deactivation->deactivate();
    }
}
