<?php
class EasystateBackgroundProcessing extends WP_Background_Process {

    protected $action = 'easystate_import_bg_properties';

    protected function task($item): bool
    {
        $key = isset($item['ListingKey']) ? absint($item[ 'ListingKey' ]) : 0;

        if ($key > 0) {
            $property_manager = new EasyStatePropertiesManager();
            $property_manager->get_property_by_listing_key($key);
            $property_manager->extract_properties(true);
        }
        return false;
    }

    protected function complete() {
        parent::complete();
        usleep(1000000);
        // Additional completion tasks if necessary
    }
}
