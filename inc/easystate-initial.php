<?php

use JetBrains\PhpStorm\NoReturn;

class EasyStateAPIPlugin
{
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_easystate_api_call', [$this, 'handle_form_submission']);
        add_action('wp_ajax_easystate_ajax_fetch_data', [$this, 'easystate_ajax_fetch_data']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

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
        <div class="easystate-admin-wrap">
            <h1 class="easystate-admin-title">EasyState API Plugin</h1>
            <form class="easystate-admin-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php
                wp_nonce_field('easystate_api_call_nonce', 'easystate_api_call_nonce_field');
                ?>
                <input type="hidden" name="action" value="easystate_api_call">
                <div class="easystate-admin-button">
                    <input id="easystate-fetch-data" type="button" class="button button-primary" value="Fetch Data">
                    <div id="loading-spinner" class="easystate-spinner"></div>
                    <div id="easy-state-timer"></div>
                </div>
            </form>
            <div id="api-data-container"></div>
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

        // Redirect back to the admin page with a success message
        wp_redirect(add_query_arg('message', 'data_fetched', admin_url('admin.php?page=easystate-api-plugin')));
        exit;
    }

    function easystate_ajax_fetch_data() {
        // Verify nonce
        check_ajax_referer('easystate_api_call_nonce', 'security');

        // handle the API call
        $properties_manager = new EasyStatePropertiesManager();
        $properties_manager->get_properties();
        $properties_manager->extract_properties();

        // Recieve data property show as log
        $api_properties = new EasyStateApiManager();
        $properties = $api_properties->fetch_data();

        if ($properties !== false) {
            wp_send_json_success($properties);
        } else {
            wp_send_json_error('Failed to fetch data from API');
        }
        wp_die();
    }


    public function enqueue_admin_scripts() {
        wp_enqueue_script('easystate-admin-script', plugin_dir_url(__FILE__) . 'js/easystate-api-admin.js', array('jquery'), '1.0', true);

        // Localize script to pass nonce and AJAX URL
        wp_localize_script('easystate-admin-script', 'easystate_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('easystate_api_call_nonce')
        ));

        wp_enqueue_style('easystate-admin-style', plugin_dir_url(__FILE__) . 'css/easystate-api-admin.css', array(), '1.0');

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
