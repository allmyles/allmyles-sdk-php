<?php
namespace Allmyles\Curl;

use stdClass;
use Exception;

class Request
{
    private $allmylesRequest;
    private $allmylesResponse;
    public $debug;

    public function __construct($fullUrl, $method, $data, $headers, $debug = false)
    {

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
        curl_setopt($this->allmylesRequest, CURLOPT_VERBOSE, (int)$this->debug);

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

    public function send($postProcessor = null)
    {
        $allmylesResponse = curl_exec($this->allmylesRequest);
        if (!$allmylesResponse) {
            throw new Exception(curl_error($this->allmylesRequest));
        }

        $response = new Response($allmylesResponse, $this);

        if (!$response->incomplete) {
            $this->close();
        }

        if ($postProcessor) {
            $response->setPostProcessor($postProcessor);
        }

        return $response;
    }

    public function getInfo($field = null)
    {
        if (!$field) {
            return curl_getinfo($this->allmylesRequest);
        } else {
            return curl_getinfo($this->allmylesRequest, $field);
        }
    }

    private function close()
    {
        return curl_close($this->allmylesRequest);
    }
}

class Response
{
    public $statusCode;
    public $error;
    public $headers;
    public $incomplete;
    public $data;
    public $postProcessor;
    private $request;

    public function __construct($allmylesResponse, $request = null, $error = false, $debug = false)
    {
        $this->request = $request;
        $this->headers = array();
        $this->postProcessor = function ($x) {return $x;};

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

        $responses = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported',
        );

        if (!isset($responses[$this->statusCode])) {
            $this->statusCode = floor($this->statusCode / 100) * 100;
        }

        switch ($this->statusCode) {
            case 200: // OK
            case 202: // Accepted
            case 204: // No Content
            case 304: // Not modified
                break;
            case 301: // Moved permanently
            case 302: // Moved temporarily
            case 307: // Moved temporarily
                break;
            default:
                $this->error = $status_message;
        }

        $this->incomplete = $this->statusCode == 202;
    }

    public function setPostProcessor($func)
    {
        $this->postProcessor = $func;
    }

    public function retry($sleepTime = 0)
    {
        sleep($sleepTime);
        return $this->request->send($this->postProcessor);
    }

    public function get()
    {
        if (is_string($this->data)) {
            $data = json_decode($this->data, true);
        } else {
            $data = $this->data;
        };

        if (!$this->incomplete) {
            return call_user_func($this->postProcessor, $data);
        };
    }

}

class Curl
{
    private $baseUrl;
    public $debug;

    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->debug = false;
    }

    public function request($endpoint, $headers, $method = 'GET', $data = null)
    {
        if ($this->debug) {
            print("==== DATA =====\n");
            print $data;
            print("\n==== END OF DATA =====\n");
        }

        $fullUrl = $this->baseUrl . '/' . $endpoint;

        $request = new Request($fullUrl, $method, $data, $headers);
        $response = $request->send();

        return $response;
    }
}
