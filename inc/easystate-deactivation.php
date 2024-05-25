<?php

class EasyStateDeactivation
{
    public function deactivate()
    {
        $this->delete_table();
    }

    private function delete_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'easystate_properties';

        $sql = "DROP TABLE IF EXISTS $table_name;";
        $wpdb->query($sql);
    }
}
