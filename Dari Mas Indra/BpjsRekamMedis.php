<?php

use GuzzleHttp\Client;
use LZCompressor\LZString;

class BpjsRekamMedis
{

    private $rme_conf_dev = [
        'kode_faskes'   => '1324R003',
        'cons_id'       => '12***',
        'secret_key'    => 'rsj***',
        'user_key'      => '363b***',
        'base_url'      => 'https://apijkn-dev.bpjs-kesehatan.go.id',
        'service_name'  => 'erekammedis_dev'
    ];

    /**
     * Guzzle HTTP Client object
     * @var \GuzzleHttp\Client
     */
    private $clients;

    /**
     * Request headers
     * @var array
     */
    private $headers;

    /**
     * X-cons-id header value
     * @var int
     */
    private $cons_id;

    /**
     * X-Timestamp header value
     * @var string
     */
    private $timestamp;

    /**
     * X-Signature header value
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $secret_key;

    /**
     * @var string
     */
    private $user_key;

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var string
     */
    private $service_name;
    private $timeout = 0;

    private $kode_faskes;

    public function __construct()
    {

        $this->clients = new Client([
            'verify' => false
        ]);

        $conf = $this->rme_conf_dev;

        foreach ($conf as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }

        //set X-Timestamp, X-Signature, and finally the headers
        $this->setTimestamp()->setSignature()->setHeaders();
    }

    protected function setHeaders()
    {
        $this->headers = [
            'X-cons-id' => $this->cons_id,
            'X-timestamp' => $this->timestamp,
            'X-signature' => $this->signature,
            'user_key' => $this->user_key
        ];
        return $this;
    }

    protected function setTimestamp()
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->timestamp = (string)$dateTime->getTimestamp();
        return $this;
    }

    protected function setSignature()
    {
        $data = $this->cons_id . '&' . $this->timestamp;
        $signature = hash_hmac('sha256', $data, $this->secret_key, true);
        $encodedSignature = base64_encode($signature);
        $this->signature = $encodedSignature;
        return $this;
    }

    private function _getDecryptionKey()
    {
        return $this->cons_id . $this->secret_key . $this->timestamp;
    }

    // returns response object or false
    private function _request($method, $feature, $data = [], $headers = [])
    {
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE']))
            return false;

        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            if (!empty($headers)) {
                //$this->headers = array_merge($this->headers, $headers);
                $this->headers['Content-Type'] = $headers;
            } else {
                $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        $opts = ['headers' => $this->headers, 'timeout' => $this->timeout];
        if (!empty($data))
            $opts['body'] = $data;

        // return $opts;
        // return false;

        try {
            $response = json_decode(
                $this->clients->request(
                    $method,
                    $this->base_url . '/' . $this->service_name . '/' . $feature,
                    $opts
                )->getBody()->getContents(),
                true
            );
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->getCode() == 0) {
                $handlerContext = $e->getHandlerContext();
                $response = [
                    'metaData' => [
                        'code' => $handlerContext['errno'],
                        'message' => $handlerContext['error']
                    ]
                ];
            } else
                $response = [
                    'metaData' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage()
                    ]
                ];
        }

        if (($response['metaData']['code'] ?? '' == '200' || $response['metadata']['code'] ?? '' == '200')
            and !empty($response['response']) and is_string($response['response'])
        )
            $response['response'] = json_decode($this->_decompress($response['response']), true);
        return $response;
    }

    private function _decompress($txt)
    {
        $key  = $this->_getDecryptionKey();
        $hash = hex2bin(hash('sha256', $key));
        $iv   = substr($hash, 0, 16);


        $tmp  = openssl_decrypt(base64_decode($txt), 'AES-256-CBC', $hash, OPENSSL_RAW_DATA, $iv);
        if ($tmp === false) return $txt;

        return LZString::decompressFromEncodedURIComponent($tmp);
    }

    public function get($feature, $headers = [])
    {
        return $this->_request('GET', $feature, $headers);
    }

    public function post($feature, $data = [], $headers = [])
    {
        return $this->_request('POST', $feature, $data, $headers);
    }

    public function put($feature, $data = [], $headers = [])
    {
        return $this->_request('PUT', $feature, $data, $headers);
    }

    public function delete($feature, $data = [], $headers = [])
    {
        return $this->_request('DELETE', $feature, $data, $headers);
    }

    public function show_headers()
    {
        return $this->headers;
    }

    #endregion

    #region BpjsRekamMedis

    public function Encrypt(string $data): string
    {
        $compressed = base64_encode(gzencode($data));

        $encrypt_key =  $this->cons_id . $this->secret_key . $this->kode_faskes;
        $keyHash = hash('sha256', $encrypt_key, true);
        $iv      = substr($keyHash, 0, 16);

        return base64_encode(openssl_encrypt($compressed, 'AES-256-CBC', $keyHash, OPENSSL_RAW_DATA, $iv));
    }

    public function insertMedicalRecord($data)
    {
        return $this->post('eclaim/rekammedis/insert', $data, 'text/plain');
    }


    #endregion
}
