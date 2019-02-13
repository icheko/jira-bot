<?php

namespace App\Services\Bamboo;

use App\Services\Api\Client;
use GuzzleHttp\Client as GuzzleClient;

class BambooApi
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
     * JiraApi constructor.
     *
     * @param GuzzleClient $guzzleClient
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __construct(GuzzleClient $guzzleClient) {
        $this->client = new Client(
            $guzzleClient, config('bot.bamboo.api_baseurl'), config('bot.bamboo.auth_username'), config('bot.bamboo.auth_password')
        );

        $this->botName = config('bot.bamboo.username');
    }
}