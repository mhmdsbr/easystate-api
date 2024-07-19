<?php

class EasyStateProperty
{
    private array $propertyData = [];

    public function __construct($property)
    {
        $this->propertyData = $property;
    }

    public function insert_property() {
        if (empty($this->propertyData)) {
            return;
        }

        $property_title = $this->propertyData['UnparsedAddress'];
        $property_description = $this->propertyData['PublicRemarks'];
        $property_id = $this->propertyData['ListingId'];
        $property_price = $this->propertyData['ListPrice'];
        $property_officename = $this->propertyData['ListOfficeName'];
        $property_mlsstatus = $this->propertyData['StandardStatus'];
        $property_land = $this->propertyData['LivingArea'];
        $property_agent_name = $this->propertyData['ListAgentFullName'];
        $property_zip_code = $this->propertyData['PostalCode'];
        $property_bedrooms = $this->propertyData['BedroomsTotal'];
        $property_bathrooms = $this->propertyData['BathroomsFull'];
        $property_garage = $this->propertyData['GarageSpaces'];
        $property_year_built = $this->propertyData['YearBuilt'];
        $property_unique_id = $this->propertyData['ListingKey'];
        $post_id = $this->get_post_id_by_listing_key($property_unique_id);
        $post_data = array(
            'post_title'   => $property_title,
            'post_type'    => 'property',
            'post_status'  => 'publish',
            'post_content' => $property_description
        );

        if ($post_id < 0) {
            $post_id = wp_insert_post($post_data);
        } else {
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
        }

        update_post_meta($post_id, 'fave_property_listing_key', $this->propertyData['ListingKey']);
        update_post_meta($post_id, 'fave_property_id', $property_id);
        update_post_meta($post_id, 'fave_property_price', $property_price);
        update_post_meta($post_id, 'fave_officename', $property_officename);
        update_post_meta($post_id, 'fave_mlsstatus', $property_mlsstatus);
        update_post_meta($post_id, 'fave_property_land', $property_land);
        update_post_meta($post_id, 'fave_agentname', $property_agent_name);
        update_post_meta($post_id, 'fave_property_zip', $property_zip_code);
        update_post_meta($post_id, 'fave_property_bedrooms', $property_bedrooms);
        update_post_meta($post_id, 'fave_property_bathrooms', $property_bathrooms);
        update_post_meta($post_id, 'fave_property_garage', $property_garage);
        update_post_meta($post_id, 'fave_property_year', $property_year_built);

        // Assign agent to the property
        $selected_agent_id = $this->get_selected_agent_id_from_settings();
        if ($selected_agent_id) {
            update_post_meta($post_id, 'fave_agent_display_option', 'agent_info');
            update_post_meta($post_id, 'fave_agents', $selected_agent_id);
        }

        // City
        if (array_key_exists("City" ,$this->propertyData)) {
            $city_term = term_exists($this->propertyData['City'], 'property_city');
            if ($city_term === 0 || $city_term === null) {
                $city_term = wp_insert_term($city_term, 'property_city');
            }
            if (!is_wp_error($post_id) && !is_wp_error($city_term)) {
                wp_set_post_terms($post_id, intval($city_term['term_id']), 'property_city');
            }
        }
        // Property Type
        if (array_key_exists("PropertyType" ,$this->propertyData)) {
            $type_term = term_exists($this->propertyData['PropertyType'], 'property_type');
            if ($type_term === 0 || $type_term === null) {
                $type_term = wp_insert_term($type_term, 'property_city');
            }
            if (!is_wp_error($post_id) && !is_wp_error($type_term)) {
                wp_set_post_terms($post_id, intval($type_term['term_id']), 'property_type');
            }
        }

        // Property Country
        if (array_key_exists("Country", $this->propertyData)) {
            $country_term = term_exists($this->propertyData['Country'], 'property_country');
            if ($country_term === 0 || $country_term === null) {
                $country_term = wp_insert_term($this->propertyData['Country'], 'property_country');
            }
            if (!is_wp_error($post_id) && !is_wp_error($country_term)) {
                $term_id = is_array($country_term) ? $country_term['term_id'] : $country_term;
                wp_set_post_terms($post_id, intval($term_id), 'property_country');
            }
        }

        // Property State
        if (array_key_exists("StateOrProvince", $this->propertyData)) {
            $state_term = term_exists($this->propertyData['StateOrProvince'], 'property_state');
            if ($state_term === 0 || $state_term === null) {
                $state_term = wp_insert_term($this->propertyData['StateOrProvince'], 'property_state');
            }
            if (!is_wp_error($post_id) && !is_wp_error($state_term)) {
                $term_id = is_array($state_term) ? $state_term['term_id'] : $state_term;
                wp_set_post_terms($post_id, intval($term_id), 'property_state');
            }
        }

        // Process media
        $this->process_media($post_id);
    }

    private function get_selected_agent_id_from_settings()
    {

        $es_api_settings = get_option('es_api_settings', '') ?? '';
        return $es_api_settings['es_agent_select'];
    }

    private function process_media($post_id) {
        if (empty($this->propertyData['Media'])) {
            return;
        }

        $media_items = $this->propertyData['Media'];
        $featured_image_set = false;

        foreach ($media_items as $media) {
            $media_url = $media['MediaURL'];
            $media_id = $this->upload_media($media_url, $post_id);
            if (!$featured_image_set) {
                set_post_thumbnail($post_id, $media_id);
                $featured_image_set = true;
            } else {
                add_post_meta($post_id, 'fave_property_images', $media_id);
            }
        }
    }

    private function upload_media($url, $post_id): bool|int
    {
        // Check if the attachment already exists
        $attachment_id = $this->get_attachment_by_url($url, $post_id);
        if ($attachment_id) {
            return $attachment_id; // Return the existing attachment ID
        }

        // Download file to temporary location
        $tmp = download_url($url);

        // Check for download errors
        if (is_wp_error($tmp)) {
            return false;
        }

        // Get file name and type
        $file_array = array();
        $file_array['name'] = basename($url);
        $file_array['tmp_name'] = $tmp;

        // Upload the file into the media library
        $media_id = media_handle_sideload($file_array, $post_id);

        // Check for handle sideload errors
        if (is_wp_error($media_id)) {
            @unlink($file_array['tmp_name']);
            return false;
        }

        return $media_id;
    }

    /**
     * Check if attachment with given URL already exists for a post.
     *
     * @param string $url     The URL of the attachment.
     * @param int    $post_id The ID of the post to which the attachment is attached.
     * @return int|false Attachment ID if found, false otherwise.
     */
    private function get_attachment_by_url($url, $post_id): bool|int
    {
        global $wpdb;

        // Construct query to find attachment ID by URL and post ID
        $query = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}posts
        WHERE post_type = 'attachment'
        AND post_parent = %d
        AND guid = %s",
            $post_id,
            $url
        );

        $attachment_id = $wpdb->get_var($query);

        if ($attachment_id) {
            return (int) $attachment_id;
        }

        return false;
    }


    function get_post_id_by_listing_key($listing_key): bool|int
    {
        global $wpdb;

        $post_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = 'fave_property_listing_key'
            AND meta_value = %s
            LIMIT 1",
                $listing_key
            )
        );

        if ($post_id) {
            return (int) $post_id;
        }

        return -1;
    }
}

