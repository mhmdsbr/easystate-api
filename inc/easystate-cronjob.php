<?php

/**
 * class is responsible for managing scheduled tasks (cron jobs) related to the  Easystate API.
 * It sets up a default weekly cron job that triggers product updates from the Easystate API.
 *
 * @since      1.0.0
 * @package    Easystate
 * @subpackage Easystate
 * @author     Easystate
 * */

class EasystateCronjob
{
    const ES_Default_Interval = 'daily';
    const ES_Default_Time = '23:00:00';


    public function __construct()
    {
        add_action('easystate_import_updates_cron', [$this, 'es_import_cron_callback']);
        add_action('wp_ajax_easystate_ajax_clear_queue', [$this, 'easystate_ajax_clear_queue']);
    }

    public static function update_cron_job()
    {
        $es_schedule_import_update = get_option('es_api_cronjob', '');
        $es_cron_interval = empty($es_schedule_import_update) ? self::ES_Default_Interval : $es_schedule_import_update[ 'es_cron_interval'];
        $es_cron_time = empty($es_schedule_import_update) ? self::ES_Default_Time : $es_schedule_import_update[ 'es_update_time' ];
        $timestamp = wp_next_scheduled('easystate_import_updates_cron');

        if ($es_cron_interval == 'null')
        {
            wp_unschedule_event($timestamp, 'easystate_import_updates_cron');
            return;
        }

        $event_time = strtotime($es_cron_time);
        if ($event_time < time()) {
            $event_time = strtotime($es_cron_time . ' + 1 day');
        }

        $current_event = wp_get_schedule('easystate_import_updates_cron');
        if ($current_event !== $es_cron_interval || $timestamp !== strtotime($es_cron_time)) {
            wp_unschedule_event($timestamp, 'easystate_import_updates_cron');
            wp_schedule_event(
                $event_time,
                $es_cron_interval,
                'easystate_import_updates_cron'
            );
        }
    }

    function es_import_cron_callback()
    {
        $properties_manager = new EasyStatePropertiesManager();
        $properties_manager->get_listing_keys();
        $properties = $properties_manager->get_properties_data();
        if(count($properties) > 0) {
            global $bg_process;
            foreach ($properties as $property) {
                $key = $property['ListingKey'];
                $bg_process->push_to_queue(['ListingKey' => $key]);
            }
            $bg_process->save()->dispatch();
        }

    }

    public static function easystate_ajax_clear_queue()
    {
        $queue_key = '_import_bg_properties';
        global $bg_process;
        $bg_process->cancel_process();
        global $wpdb;
        $option_value_key = '%$queue_key%';
        $sql = "DELETE FROM {$wpdb->options}
            WHERE option_name LIKE %s";
        es_log($sql);
        $results = $wpdb->get_results($wpdb->prepare($sql, $option_value_key));

        es_log('queue cleared');
        wp_send_json_success('queue cleared successfully');

    }


    }
