<?php

namespace App\Services\BitbucketServer;

use App\Services\Api\Client;
use GuzzleHttp\Client as GuzzleClient;

class BitbucketServerApi
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    private $botName;

    /**
     * BitbucketServerApi constructor.
     *
     * @param GuzzleClient $guzzleClient
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __construct(GuzzleClient $guzzleClient) {
        $this->client = new Client(
            $guzzleClient, config('bot.bitbucket-server.api_baseurl'), config('bot.bitbucket-server.auth_username'), config('bot.bitbucket-server.auth_password')
        );

        $this->botName = config('bot.bitbucket-server.username');
    }

    /**
     * @return mixed
     */
    public function getBranches($filter){

        $response = $this->client->request('GET', "projects/STARS20/repos/master/branches?filterText={$filter}");

        if($response->getStatusCode() != 200){
            // log exception
        }

        return json_decode($response->getBody());
    }
}