<?php
require_once __DIR__ . '/BYHttpClient.php';

class BYGetTokenClient{

    private static $request_url = 'https://open.byai.com/oauth/token';

    public function __construct($client_id, $client_secret, $access_token = NULL, $refresh_token = NULL) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }

    /**
     * 获取access_token
     */
    public function get_token( $grant_type, $company_id ) {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        $params['grant_type'] = $grant_type;
        $params['company_id'] = $company_id;


        return $this->parse_response(
            BYHttpClient::post(self::$request_url, $params)
        );
    }

    private function parse_response($response_data) {
        $data = json_decode($response_data, true);
        if (null === $data) throw new Exception('response invalid, data: ' . $response_data);
        return $data;
    }
}