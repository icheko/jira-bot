<?php

namespace App\Services\Api;

use GuzzleHttp\Client as GuzzleClient;
use \Log;

class Client
{
    /**
     * @var GuzzleClient
     */
    public $client;

    /**
     * Client constructor.
     *
     * @param GuzzleClient $client
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __construct($url, $username, $password) {

        $this->client = new GuzzleClient([
                           // Base URI is used with relative requests
                           'base_uri' => $url,
                           'auth' => [
                               $username,
                               $password
                           ],
                           'headers' => [
                               'Accept' => 'application/json',
                               'Content-Type' => 'application/json'
                           ],
                       ]);
    }

    /**
     * @param $text
     */
    public function log($class, $text){
        Log::info("[{$class}]: {$text}");
    }

    /**
     * @param $text
     */
    public function error($class, $text){
        Log::error("[{$class}]: {$text}");
    }

    /**
     * Handle dynamic method calls into the GuzzleClient.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->client->$method(...$parameters);
    }
}