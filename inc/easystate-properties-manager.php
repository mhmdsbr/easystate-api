<?php

class EasyStatePropertiesManager
{
    private EasyStateApiManager $api_manager;
    private array $propertiesData = [];

    public function __construct()
    {
        $this->api_manager = new EasyStateApiManager();
    }

    public function get_properties() {
        $page = 1;
        $per_page = 20;
        $fields = '';
        $filters = '';
        $orderby = 'ASC';

        $this->propertiesData = $this->api_manager->fetch_data($page, $per_page, $fields, $filters, $orderby);
    }

    public function extract_properties() {
        foreach ($this->propertiesData as $propertyData) {
            $property = new EasyStateProperty($propertyData);
            $property->insert_property();
        }
    }


}



