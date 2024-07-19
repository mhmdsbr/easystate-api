<?php

class EasyStatePropertiesManager
{
    private EasyStateApiManager $api_manager;
    private $property;
    private array $propertiesData = [];

    public function __construct()
    {
        $this->api_manager = new EasyStateApiManager();
    }

    public function get_properties() {
        $es_api_settings = get_option('es_api_settings', '') ?? '';
        $page = 1;
        $per_page = empty($es_api_settings) ? 10 : $es_api_settings['es_per_page'];
        $fields = empty($es_api_settings) ? 'ALL' : $es_api_settings['es_fields_multi_check'];
        $filters = empty($es_api_settings) ? '' : $es_api_settings['es_api_filters'];

        if (!$fields) {
            $fields = 'ALL';
        } else {
            $fields = array_merge($fields, ['ListingKey' => 'ListingKey', 'Media' => 'Media']);
            $fields = array_filter($fields);
            $fields = implode(",", $fields);
        }

        $orderby = 'ASC';

        $this->propertiesData = $this->api_manager->fetch_data($page, $per_page, $fields, $filters, $orderby);
    }

    public function get_listing_keys() {
        $es_api_settings = get_option('es_api_settings', '') ?? '';
        $page = 1;
        $per_page = empty($es_api_settings) ? 10 : $es_api_settings['es_per_page'];
        $fields = 'ListingKey';
        $filters = empty($es_api_settings) ? '' : $es_api_settings['es_api_filters'];

        $this->propertiesData = $this->api_manager->fetch_data($page, $per_page, $fields, $filters);

    }

    public function get_property_by_listing_key($listingkey) {
        $es_api_settings = get_option('es_api_settings', '') ?? '';
        $fields = empty($es_api_settings) ? 'ALL' : $es_api_settings['es_fields_multi_check'];

        if (!$fields) {
//            $fields = ['ListingKey' => 'ListingKey', 'Media' => 'Media'];
              $fields = '';
        } else {
            $fields = array_merge($fields, ['ListingKey' => 'ListingKey', 'Media' => 'Media']);
            $fields = array_filter($fields);
            $fields = implode(",", $fields);
        }


        $this->property = $this->api_manager->fetch_data_by_listing_key($listingkey, $fields);
    }

    public function extract_properties($is_single = false) {
        if($is_single) {
            $property = new EasyStateProperty($this->property);
            $property->insert_property();
        } else {
            foreach ($this->propertiesData as $propertyData) {
                $property = new EasyStateProperty($propertyData);
                $property->insert_property();
            }
        }
    }

    public function get_properties_data(): array
    {
        return $this->propertiesData;
    }

}



