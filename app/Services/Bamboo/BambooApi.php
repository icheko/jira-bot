<?php

namespace App\Services\Bamboo;

use App\Services\Api\Client;
use GuzzleHttp\Client as GuzzleClient;
use \Log;

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

    public function createPlanBranch($branch){
        $branch_friendly = preg_replace("/\//", '-', $branch);
        $this->log("Creating plan branch [{$branch_friendly}] in Bamboo");

        $response = $this->client->request('PUT', "plan/POR-POUI/branch/{$branch_friendly}?vcsBranch={$branch}&enabled=true&cleanupEnabled=true");

        if($response->getStatusCode() != 200){
            // log exception
        }

        return json_decode($response->getBody());
    }

    /**
     * @param $text
     */
    private function log($text){
        Log::info("[BambooApi]: {$text}");
    }
}