<?php

use JetBrains\PhpStorm\NoReturn;

/**
 *
 *
 *
 *
 *
 *
 * @link       https://www.easystate.com
 * @since      1.0.0
 *
 * @package    eaasystate
 * @subpackage easystate
 */

class EasystateSetting
{
    private EasystateTools $settings_api;

    public function __construct()
    {
        $this->settings_api = new EasystateTools();
        add_action('admin_init', [$this, 'setting_init']);
    }

    public function setting_init()
    {
        $clear_queue_button = '<button type="button" class="button button-secondary clear_queue" name="clear_queue" id="clear_queue">' . __('Clear queue') . '</button>';
        $check_api_credentials = sprintf(
            '<button type="button" class="button button-secondary check_api_credentials" name="check_api_credentials" id="check_api_credentials">%s</button>',
            __('Validate Credentials', 'easy-state')
        );

        $sections = [
            [
                'id'    => 'es_api_credentials',
                'title' => __('Credentials', 'easy-state'),
            ],
            [
                'id'    => 'es_api_settings',
                'title' => __('Api Setting', 'easy-state'),
            ],
            [
                'id'    => 'es_api_cronjob',
                'title' => __('Api Cronjob', 'easy-state'),
            ],
            [
                'id'    => 'es_api_clear_queue',
                'title' => __('Api Clear import process', 'easy-state'),
            ],
        ];

        $agent_options = $this->get_houzez_agent_titles();

        $fields = [
            'es_api_credentials' => [
                [
                    'name'    => 'es_client_id',
                    'label'   => 'Client ID',
                    'desc'    => '',
                    'type'    => 'text',
                    'default' => ''
                ],
                [
                    'name'    => 'es_client_secret',
                    'label'   => 'Client Secret',
                    'desc'    => '',
                    'type'    => 'text',
                    'default' => ''
                ],
                [
                    'name'    => 'es_api_key',
                    'label'   => 'API Key',
                    'desc'    => '',
                    'type'    => 'text',
                    'default' => ''
                ],
                [
                    'name'    => 'es_api_check_credentials_btn',
                    'label'   => __('Validate', 'easy-state'),
                    'desc'    => $check_api_credentials,
                    'type'    => 'html',
                    'default' => ''
                ],
            ],
            'es_api_settings' => [
                [
                    'name'              => 'es_per_page',
                    'label'             => __('Items per page', 'easy-state'),
                    'desc'              => __('Number of items per page', 'easy-state'),
                    'placeholder'       => __('1', 'easy-state'),
                    'min'               => 0,
                    'max'               => 100,
                    'step'              => '1',
                    'type'              => 'number',
                ],
                [
                    'name'    => 'es_fields_multi_check',
                    'label'   => __('Choose fields', 'easy-state'),
                    'desc'    => __('Leave it empty if you want all data to be retrieved', 'easy-state'),
                    'type'    => 'multicheck',
                    'options' => [
                        'UnparsedAddress'   => 'UnparsedAddress',
                        'City'   => 'City',
                        'Country'   => 'Country',
                        'StateOrProvince' => 'StateOrProvince',
                        'PublicRemarks'   => 'PublicRemarks',
                        'PropertyType'   => 'PropertyType',
                        'BedroomsTotal'   => 'BedroomsTotal',
                        'ListPrice' => 'ListPrice',
                        'ListOfficeName' => 'ListOfficeName',
                        'StandardStatus' => 'StandardStatus',
                        'LivingArea' => 'LivingArea',
                        'ListAgentFullName' => 'ListAgentFullName',
                        'PostalCode'=> 'PostalCode',
                        'BathroomsFull' => 'BathroomsFull',
                        'GarageSpaces' => 'GarageSpaces',
                        'YearBuilt' => 'YearBuilt',
                    ]
                ],
                [
                    'name'    => 'es_api_filters',
                    'label'   => 'Filter Property Data',
                    'desc'    => __('Please follow MLS api doc to add the proper keywords.', 'easy-state'),
                    'type'    => 'text',
                    'placeholder' => 'e.g. (ListPrice lt 300000 and LotSizeAcres lt 3) or (BedroomsTotal gt 4 and StandardStatus eq \'Active\')',
                ],
                [
                    'name'    => 'es_agent_select',
                    'label'   => __('Select Agent', 'easy-state'),
                    'desc'    => __('Choose an agent from the list', 'easy-state'),
                    'type'    => 'select',
                    'options' => $agent_options,
                ],
            ],
            'es_api_cronjob' => [
                [
                    'name'    => 'es_cron_interval',
                    'label'   => __('Interval', 'easy-state'),
                    'desc'    => '',
                    'type'    => 'select',
                    'default' => 'daily',
                    'options' => [
                        'null' => '',
                        'hourly' => 'Hourly',
                        'daily'   => 'Daily',
                        'weekly'  => 'Weekly',
                        'monthly' => 'Monthly',
                    ],
                ],
                [
                    'name'    => 'es_update_time',
                    'label'   => 'Update time',
                    'desc'    => __('Please add the time of update as example. Be note that it does not work for Hourly option', 'easy-state'),
                    'type'    => 'text',
                    'default' => '23:00:00',
                    'placeholder' =>'23:59:59',
                ],
            ],
            'es_api_clear_queue' => [
                [
                    'name'    => 'es_api_import_clear_button',
                    'label'   => __('Cancel', 'easy-state'),
                    'desc'    => $clear_queue_button,
                    'type'    => 'html',
                    'default' => ''
                ],
            ],
        ];

        $this->settings_api->set_sections($sections);
        $this->settings_api->set_fields($fields);
        $this->settings_api->admin_init();
    }

    function get_houzez_agent_titles(): array
    {
        $args = [
            'post_type' => 'houzez_agent',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'fields' => 'ids',
        ];

        $query = new WP_Query($args);
        $agent_options = [];
        $agent_options[''] = '';

        foreach ($query->posts as $post_id) {
            $post_title = get_the_title($post_id);
            $agent_options[$post_id] = $post_title;
        }
        return $agent_options;
    }

    public function render_setting_page()
    {
        echo '';
        settings_errors();
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        if (isset($_REQUEST['settings-updated']) and $_REQUEST['settings-updated'] == true) {
            EasystateCronjob::update_cron_job();
        }

    }
}
