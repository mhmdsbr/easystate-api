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
        $property_unique_id = $this->propertyData['ListingKey'];
        $post_id = $this->get_post_id_by_listing_key($property_unique_id);
        error_log($post_id);
        if ($post_id < 0) {
            $post_id = wp_insert_post(array(
                'post_title' => $property_title,
                'post_type' => 'property',
                'post_status' => 'publish',
            ));
        }

        $city_term = term_exists($this->propertyData['City'], 'property_city');
        if ($city_term === 0 || $city_term === null) {
            $city_term = wp_insert_term($city_term, 'property_city');
        }

        if (!is_wp_error($post_id) && !is_wp_error($city_term)) {
            wp_set_post_terms($post_id, intval($city_term['term_id']), 'property_city');
        }

        update_post_meta($post_id, 'fave_property_listing_key', $this->propertyData['ListingKey']);

        // Process media
        $this->process_media($post_id);
    }

    private function process_media($post_id) {
        if (empty($this->propertyData['Media'])) {
            return;
        }

        $media_items = $this->propertyData['Media'];
        $featured_image_set = false;
        $gallery_ids = [];
//        $i = 0;
        foreach ($media_items as $media) {
//            $i++;
//            if($i>2) {
//                break;
//            }
            $media_url = $media['MediaURL'];
            $media_id = $this->upload_media($media_url, $post_id);
            if (!$featured_image_set) {
                set_post_thumbnail($post_id, $media_id);
                $featured_image_set = true;
            } else {
                $gallery_ids[] = $media_id;
            }
        }

        if (!empty($gallery_ids)) {
            foreach($gallery_ids as $gallery_item) {
                update_post_meta($post_id, 'fave_property_images', $gallery_item);
            }
        }
    }

    private function upload_media($url, $post_id): bool|int
    {
        // First, check if the attachment already exists
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

