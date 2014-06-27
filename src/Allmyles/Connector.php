<?php
namespace Allmyles\Connector;

require 'Curl.php';

class ServiceConnector
{
    protected $curl;
    protected $authKey;
    protected $retryDelay = 5;  // in seconds
    protected $maxRetryCount = 10;

    public function __construct($baseUrl, $authKey)
    {
        $this->curl = new \Allmyles\Curl\Curl($baseUrl);
        $this->authKey = $authKey;
    }

    public function get($endpoint, $context, $params = null)
    {
        $headers = array(
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "Cookie" => $context->session,
            "X-Auth-Token" => $this->authKey,
        );

        if ($params) {
            $separator = '?';
            foreach ($params as $key => $value) {
                $endpoint .= $separator . urlencode($key) . '=' . urlencode($value);
                $separator = '&';
            };
        };

        $response = $this->curl->request($endpoint, $headers);

        return $response;
    }

    public function post($endpoint, $context, $data = null)
    {
        $headers = array(
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "Cookie" => $context->session,
            "X-Auth-Token" => $this->authKey,
        );

        $response = $this->curl->request($endpoint, $headers, 'POST', $data);

        return $response;
    }
}
