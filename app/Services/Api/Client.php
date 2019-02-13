<?php

namespace App\Services\Api;

use GuzzleHttp\Client as GuzzleClient;

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
    public function __construct(GuzzleClient $client, $url, $username, $password) {

        $this->client = new GuzzleClient([
                           // Base URI is used with relative requests
                           'base_uri' => $url,
                           'auth' => [
                               $username,
                               $password
                           ],
                       ]);
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