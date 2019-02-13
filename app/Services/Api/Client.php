<?php

namespace App\Services\Api;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * Client constructor.
     *
     * @param GuzzleClient $client
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __construct(GuzzleClient $client) {

        $this->client = new GuzzleClient([
                           // Base URI is used with relative requests
                           'base_uri' => config('jira-bot.api_baseurl'),
                           'auth' => [config('jira-bot.auth_username'), config('jira-bot.auth_password')],
                       ]);

//        $res = $this->client->request('GET', 'search?jql=(text%20~%20woof)%20AND%20updatedDate%20>%3D%20-7d%20ORDER%20BY%20updated%20DESC&fields=comment');
//        echo $res->getStatusCode();
//        echo $res->getHeader('content-type')[0];
//        echo $res->getBody();
    }
}