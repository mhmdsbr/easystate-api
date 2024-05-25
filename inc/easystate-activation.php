<?php

class EasyStateActivation
{
    public function activate()
    {
        $this->create_table();
        $dataFetcher = new EasyStateDataFetcher();
        $dataFetcher->fetch_data();
    }

    private function create_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'easystate_properties';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                listing_key varchar(100) NOT NULL,
                listing_id varchar(100) NOT NULL,
                property_type varchar(100) NOT NULL,
                standard_status varchar(50) NOT NULL,
                list_price decimal(10,2) NOT NULL,
                city varchar(100) NOT NULL,
                unparsed_address varchar(100) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
