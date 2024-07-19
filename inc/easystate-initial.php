<?php

use JetBrains\PhpStorm\NoReturn;

class EasyStateAPIPlugin
{
    private EasystateSetting $easystateSetting;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_easystate_api_call', [$this, 'handle_form_submission']);
        add_action('wp_ajax_easystate_ajax_fetch_data', [$this, 'easystate_ajax_fetch_data']);
        add_action('wp_ajax_easystate_ajax_check_credentials', [$this, 'easystate_ajax_check_credentials']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        $this->easystateSetting = new EasystateSetting();
    }

    public function add_admin_menu() {
        add_menu_page(
            'EasyState API Plugin',
            'EasyState API',
            'manage_options',
            'easystate-api-plugin',
            [$this, 'create_admin_page'],
            'dashicons-database-import',
            90
        );
        add_submenu_page(
            'easystate-api-plugin',
            'Easy State Setting',
            'Settings',
            'manage_options',
            'easystate_options',
            array($this->easystateSetting, 'render_setting_page')
        );
    }

    public function create_admin_page() {
        ?>
        <div class="easystate-admin-wrap">
            <h1 class="easystate-admin-title">Fetch Properties</h1>
            <span>Please note that this button is only used for testing purposes and does not support auto update. Use this button to check if the data connection and data retrieval is working ok.</span>
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
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        if (!isset($_POST['easystate_api_call_nonce_field']) || !wp_verify_nonce($_POST['easystate_api_call_nonce_field'], 'easystate_api_call_nonce')) {
            wp_die(__('Nonce verification failed.'));
        }

        wp_redirect(add_query_arg('message', 'data_fetched', admin_url('admin.php?page=easystate-api-plugin')));
        exit;
    }

    #[NoReturn] function easystate_ajax_fetch_data() {
        check_ajax_referer('easystate_api_call_nonce', 'security');

        $properties_manager = new EasyStatePropertiesManager();
        $properties_manager->get_properties();
        $properties_manager->extract_properties();

        if ($properties_manager->get_properties_data()) {
            wp_send_json_success($properties_manager->get_properties_data());
        } else {
            wp_send_json_error('Failed to fetch data from API');
        }
        wp_die();
    }

    public function enqueue_admin_scripts() {
//        wp_enqueue_script('jquery');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
        wp_enqueue_script('easystate-admin-script', plugin_dir_url(__FILE__) . 'js/easystate-api-admin.js', array('jquery', 'select2'), '1.0', true);

        // Localize script to pass nonce and AJAX URL
        wp_localize_script('easystate-admin-script', 'easystate_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('easystate_api_call_nonce')
        ));

        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
        wp_enqueue_style('easystate-admin-style', plugin_dir_url(__FILE__) . 'css/easystate-api-admin.css', array(), '1.0');

    }

    public static function easystate_ajax_check_credentials()
    {
        $client_id = isset($_POST['es_client_id']) ? sanitize_text_field($_POST['es_client_id']) : '';
        $client_secret = isset($_POST['es_client_secret']) ? sanitize_text_field($_POST['es_client_secret']) : '';
        $apiManager = new EasyStateApiManager();
        $apiToken = $apiManager->get_api_token($client_id, $client_secret);
        es_log($apiToken);

        if (!is_string($apiToken) || empty($apiToken)) {
            wp_send_json_error('Something is wrong. Please review your client id and secret');
        } else {
            wp_send_json_success('Cool. Your credentials are all ok');
        }
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
