<?php

class EasyStateDataStorage
{
    public function store_data($properties)
    {
        error_log('EasyStateDataStorage store_data started.');
//        error_log(print_r($properties, true));
        if (!is_array($properties)) {
            error_log('Properties data is not an array.');
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'easystate_properties';

        foreach ($properties as $property) {

            $wpdb->replace(
                $table_name,
                array(
                    'listing_key' => $property['ListingKey'],
                    'listing_id' => $property['ListingId'],
                    'property_type' => $property['PropertyType'],
                    'standard_status' => $property['StandardStatus'],
                    'list_price' => $property['ListPrice'],
                    'city' => $property['City'],
                )
            );
        }

        $this->insert_properties($properties);
        error_log('EasyStateDataStorage store_data finished.');
    }


    private function insert_properties($properties)
    {
        foreach ($properties as $property) {
            $city_term = term_exists($property['City'], 'property_city');

            if ($city_term === 0 || $city_term === null) {
                $city_term = wp_insert_term($property['City'], 'property_city');
            }

            $post_id = wp_insert_post(array(
                'post_title' => $property['ListingKey'],
                'post_type' => 'property',
                'post_status' => 'publish',
            ));

            if (!is_wp_error($post_id) && !is_wp_error($city_term)) {
                wp_set_post_terms($post_id, intval($city_term['term_id']), 'property_city');
            }

        }
    }
}
