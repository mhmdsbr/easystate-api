<?php
/*
Plugin Name: EasyState API Plugin
Plugin URI: https://easystate.com/easystate-api
Description: A plugin to fetch and display data from the Realtyna MLS router API
Version: 1.0
Author: Easystate
Author URI: https://easystate.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('EASYSTATE_API_PLUGIN', plugin_dir_path(__FILE__));

require_once EASYSTATE_API_PLUGIN . 'inc/easystate-initial.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-activation.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-deactivation.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-tools.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-setting.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-api-manager.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-properties-manager.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-property.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-cronjob.php';
require_once EASYSTATE_API_PLUGIN . 'vendor/autoload.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-background-processing.php';

// Initialize the plugin
new EasystateCronjob();
new EasystateTools();
$bg_process = new EasystateBackgroundProcessing();
$easyStateAPIPlugin = new EasyStateAPIPlugin();


// Register hooks
register_activation_hook(__FILE__, [$easyStateAPIPlugin, 'plugin_activation']);
register_deactivation_hook(__FILE__, [$easyStateAPIPlugin, 'plugin_deactivation']);


function es_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}