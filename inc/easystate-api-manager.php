<?php

class EasyStateApiManager
{
    private string $api_url;
    private string $api_key;
    private string $auth_token;

    public function __construct()
    {
        $this->api_url = get_field('realtyna_url', 'option');
        $this->api_key = get_field('realtyna_api', 'option');
        $this->auth_token = get_field('realtyna_api_token', 'option');

    }
    public function fetch_data($page = 1, $per_page = 20, $fields = 'ALL', $filters = '', $orderby = 'ASC')
    {
        $url = $this->api_url . "?select=ALL&top=5";

        $response = wp_remote_get($url, array(
            'timeout'     => 500,
            'headers'     => array(
                'x-api-key' => $this->api_key,
                'Authorization' => $this->auth_token
            ),
        ));

        if (is_wp_error($response)) {
            error_log('Error fetching data from API: ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($status_code == 200) {
            $properties = json_decode($body, true);
            if (isset($properties['value']) && !empty($properties['value'])) {
                error_log('Properties fetched: ' . count($properties['value']));
                return $properties['value'];
            } else {
                error_log('API response empty or invalid');
                return false;
            }
        } else {
            error_log('API returned status code: ' . $status_code);
            return false;
        }
    }

}

