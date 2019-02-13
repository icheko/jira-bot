<?php

namespace App\Services\Jira;

use App\Services\Api\Client;
use GuzzleHttp\Client as GuzzleClient;

class JiraApi
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
            $guzzleClient, config('bot.jira.api_baseurl'), config('bot.jira.auth_username'), config('bot.jira.auth_password')
        );

        $this->botName = config('bot.jira.username');
    }

    /**
     * @return mixed
     */
    public function getCommentMentions(){

        $res = $this->client->request('GET', "search?jql=(text%20~%20{$this->botName})%20AND%20updatedDate%20>%3D%20-7d%20ORDER%20BY%20updated%20DESC&fields=comment");

        if($res->getStatusCode() != 200){
            // log exception
        }

        return $res;
    }
}