<?php
/**
 * A simple PHP client for Trext.me API
 *  
 * @author Parham Ghaffarian <parham@gouconnect.com>
 * @version 0.1
 */

require_once 'Trext/Tree.php';
require_once 'Trext/Run.php';

class Trext {
    
    public $api_user;
    public $api_secret;
    public $ch;
    public $root = 'https://app.trext.me/api/';
    public $debug = false;

    public function __construct($api_user = null, $api_secret = null, $user_agent = 'Trext PHP v0.1') {
        $this->api_user = $api_user;
        $this->api_secret = $api_secret;

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);

        $this->tree = new Trext_Tree($this);
        $this->run = new Trext_Run($this);
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function call($url, $params, $method = 'get') {

        $ch = $this->ch;
        switch ($method) {
            case 'get':
                if ( !empty($params) ){
                    $url .= '?' . http_build_query($params);
                }
                break;
            
            case 'post':
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_POST, true);
                break;
        }

        curl_setopt($ch, CURLOPT_URL, $this->root . $url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->api_user . ":" . $this->api_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);

        $start = microtime(true);
        $this->log('Call to ' . $this->root . $url . ': ' . json_encode($params));
        if($this->debug) {
            $curl_buffer = fopen('php://memory', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $curl_buffer);
        }

        $response_body = curl_exec($ch);
        $info = curl_getinfo($ch);
        $time = microtime(true) - $start;
        if($this->debug) {
            rewind($curl_buffer);
            $this->log(stream_get_contents($curl_buffer));
            fclose($curl_buffer);
        }
        $this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');
        $this->log('Got response: ' . $response_body);

        if(curl_error($ch)) {
            throw new Exception("API call to $url failed: " . curl_error($ch));
        }

        if(floor($info['http_code'] / 100) >= 4) {
            throw new Exception("API call to $url failed, http_code: " . $info['http_code']);;
        }

        $result = json_decode($response_body);

        if( $result === null )
            throw new Exception('We were unable to decode the JSON response from the Trext API: ' . $response_body);
        

        return $result;
    }

    public function log($msg) {
        if($this->debug) error_log($msg);
    }
}


