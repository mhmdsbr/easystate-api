<?php

class EasyStateDataFetcher
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

    public function fetch_data()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'x-api-key: ' . $this->api_key,
                'Authorization: ' . $this->auth_token
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            error_log('Error fetching data from API: ' . curl_error($curl));
        }

        curl_close($curl);

        $properties = json_decode($response, true);

        if (isset($properties['value']) && !empty($properties['value'])) {
            error_log('Properties fetched: ' . count($properties['value']));
            $dataStorage = new EasyStateDataStorage();
            $dataStorage->store_data($properties['value']);
        } else {
            error_log('API response empty or invalid');
        }
    }
}

