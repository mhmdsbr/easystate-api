<?php

class EasyStateApiManager
{
    private string $api_url;
    private string $api_key;
    private string $auth_token;
    private string $client_id = '';
    private string $client_secret = '';

    public function __construct() {
        $es_api_credentials = get_option('es_api_credentials', '');
        // Ensure $es_api_credentials is an array
        if (!is_array($es_api_credentials)) {
            $es_api_credentials = array();
        }
        $this->api_url = 'https://api.realtyfeed.com/reso/odata/Property';
        $this->api_key = $es_api_credentials['es_api_key'] ?? '';
        $this->auth_token = $es_api_credentials['es_api_token'] ?? '';
        $this->client_id = $es_api_credentials['es_client_id'] ?? '';
        $this->client_secret = $es_api_credentials['es_client_secret'] ?? '';
    }
    public function fetch_data($page = 1, $per_page = 10, $fields = 'ALL', $filters = '', $orderby = 'ASC')
    {
        $top = max(1, min(200, $per_page));
        $skip = ($page - 1) * $top;

        $url = $this->api_url . "?select=$fields&top=$top&skip=$skip&orderby=$orderby";

        if (!empty($filters)) {
            $url .= "&filter=" . urlencode($filters);
        }

        $this->auth_token = $this->get_api_token();
        if (!is_string($this->auth_token) || empty($this->auth_token)) {
            error_log('Invalid API token');
            return false;
        }

        $response = wp_remote_get($url, array(
            'timeout'     => 500,
            'headers'     => array(
                'x-api-key' => $this->api_key,
                'Authorization' => 'Bearer ' . $this->auth_token
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

    public function fetch_data_by_listing_key($listingkey, $fields = 'ALL')
    {

        $url = $this->api_url . "('$listingkey')" . "?select=$fields";

        $this->auth_token = $this->get_api_token();
        if (!is_string($this->auth_token) || empty($this->auth_token)) {
            error_log('Invalid API token');
            return false;
        }

        $response = wp_remote_get($url, array(
            'timeout'     => 500,
            'headers'     => array(
                'x-api-key' => $this->api_key,
                'Authorization' => 'Bearer ' . $this->auth_token
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
            if (isset($properties['ListingKey']) && !empty($properties['ListingKey'])) {
                return $properties;
            } else {
                error_log('API response empty or invalid');
                return false;
            }
        } else {
            error_log('API returned status code: ' . $status_code);
            return false;
        }
    }



    /**
     * Retrieves an API token based on client ID and secret.
     *
     * @param string $client_id The client ID.
     * @param string $client_secret The client secret.
     * @return string|WP_Error The API token on success, or a WP_Error on failure.
     */
    function get_api_token(string $client_id= '', string $client_secret = ''): WP_Error|string
    {

        // The endpoint URL
        $url = 'https://realtyfeed-sso.auth.us-east-1.amazoncognito.com/oauth2/token';
        $client_id = empty($client_id) ? $this->client_id : $client_id;
        $client_secret = empty($client_secret) ? $this->client_secret : $client_secret;

        // The request arguments
        $args = array(
            'body' => array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'client_credentials',
            ),
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'timeout' => 30,
        );

        // Make the HTTP request
        $response = wp_remote_post( $url, $args );

        // Check for errors
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Parse the response
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // Check for valid token in the response
        if ( isset( $data['access_token'] ) ) {
            return $data['access_token'];
        }

        return new WP_Error( 'token_retrieval_failed', 'Failed to retrieve access token', $data );
    }

}

