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
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-data-fetcher.php';
require_once EASYSTATE_API_PLUGIN . 'inc/easystate-data-storage.php';

// Initialize the plugin
$easyStateAPIPlugin = new EasyStateAPIPlugin();

// Register hooks
register_activation_hook(__FILE__, [$easyStateAPIPlugin, 'plugin_activation']);
register_deactivation_hook(__FILE__, [$easyStateAPIPlugin, 'plugin_deactivation']);

if( function_exists('acf_add_options_page') ) {

    acf_add_options_page(array(
        'page_title'    => 'Realtyna API',
        'menu_title'    => 'Realtyna API Setting',
        'menu_slug'     => 'realtyna-api-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));

    acf_add_options_sub_page(array(
        'page_title'    => 'Credentials',
        'menu_title'    => 'Credentials',
        'parent_slug'   => 'realtyna-api-settings',
    ));

}