<?php
namespace Allmyles\Curl;

use Allmyles\Common\PostProcessor;
use Allmyles\Context;
use Allmyles\Exceptions\ServiceException;
use Exception;

class Request
{
    private $allmylesRequest;
    public $args;

    public function __construct($fullUrl, $method, $data, $headers)
    {
        $this->args = Array($fullUrl, $method, $data, $headers);
        $uri = parse_url($fullUrl);

        if ($uri == false) {
            throw new Exception('Unable to parse URL');
        }

        if (!isset($uri['scheme'])) {
            throw new Exception('Missing URI schema');
        }

        $this->allmylesRequest = curl_init();

        curl_setopt($this->allmylesRequest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->allmylesRequest, CURLOPT_URL, $fullUrl);
        curl_setopt($this->allmylesRequest, CURLOPT_HEADER, 1);

        $headers['User-Agent'] = ALLMYLES_VERSION;

        $curl_header = array();
        foreach ($headers as $k=>$v) {
            $curl_header[] = $k.": ".$v;
        }
        curl_setopt($this->allmylesRequest, CURLOPT_HTTPHEADER, $curl_header);

        if (isset($uri['port'])) {
            curl_setopt($this->allmylesRequest, CURLOPT_PORT, $uri['port']);
        }

        if ($uri['scheme'] == 'https') {
            curl_setopt($this->allmylesRequest, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($this->allmylesRequest, CURLOPT_SSL_VERIFYPEER, FALSE);
        }

        switch ($method) {
            case 'GET':
                curl_setopt($this->allmylesRequest, CURLOPT_HTTPGET, 1);
                break;

            case 'POST':
                curl_setopt($this->allmylesRequest, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($this->allmylesRequest, CURLOPT_POSTFIELDS, $data);
                break;

            case 'DELETE':
                curl_setopt($this->allmylesRequest, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            default:
                throw new Exception('Method not implemented.');
        }
    }

    public function send()
    {
        $allmylesResponse = curl_exec($this->allmylesRequest);
        if (!$allmylesResponse) {
            throw new Exception(curl_error($this->allmylesRequest));
        }

        $response = new Response($allmylesResponse, $this);

        if (!$response->incomplete) {
            $this->close();
        }

        return $response;
    }

    public function getInfo($field)
    {
        return curl_getinfo($this->allmylesRequest, $field);
    }

    private function close()
    {
        curl_close($this->allmylesRequest);
    }
}

class Response
{
    public $statusCode;
    public $headers;
    public $incomplete;
    public $data;
    public $postProcessor;
    public $state;
    private $request;

    public function __construct($allmylesResponse, Request $request)
    {
        $this->request = $request;
        $this->headers = array();
        $this->postProcessor = new PostProcessor();

        list($split, $this->data) = explode("\r\n\r\n", $allmylesResponse, 2);
        $split = preg_split("/\r\n|\n|\r/", $split);

        list($this->protocol, $this->statusCode, $this->status_message) = explode(' ', trim(array_shift($split)), 3);

        while ($line = trim(array_shift($split))) {
            list($header, $value) = explode(':', $line, 2);
            if (isset($this->headers[$header]) && $header == 'Set-Cookie') {
                // RFC 2109: the Set-Cookie this header comprises the token Set-
                // Cookie:, followed by a comma-separated list of one or more cookies.
                $this->headers[$header] .= ',' . trim($value);
            } else {
                $this->headers[$header] = trim($value);
            }
        }

        $this->statusCode = $this->request->getInfo(CURLINFO_HTTP_CODE);
        $this->incomplete = $this->statusCode == 202;
    }

    public function retry($sleepTime = 0)
    {
        sleep($sleepTime);
        return $this->request->send($this->postProcessor);
    }

    public function get()
    {
        if (!($this->statusCode < 400)) {
            throw new ServiceException($this->data, $this->statusCode);
        };

        if (is_string($this->data)) {
            $data = json_decode($this->data, true);
        } else {
            $data = $this->data;
        };

        if (!$this->incomplete) {
            return $this->postProcessor->process($data);
        } else {
            return null;
        }
    }

    public function saveState()
    {
        $this->state = $this->request->args;
    }

    public function restoreState()
    {
        $this->request = new Request(
            $this->state[0], $this->state[1], $this->state[2], $this->state[3]
        );
    }
}

class Curl
{
    private $baseUrl;

    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function request($endpoint, $headers, $method = 'GET', $data = null)
    {
        $fullUrl = $this->baseUrl . '/' . $endpoint;

        $request = new Request($fullUrl, $method, $data, $headers);
        $response = $request->send();

        return $response;
    }
}
