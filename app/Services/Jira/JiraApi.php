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
            config('bot.jira.api_baseurl'), config('bot.jira.auth_username'), config('bot.jira.auth_password')
        );

        $this->botName = config('bot.jira.username');
    }

    /**
     * Get all comments tagged with the bot name
     * @return mixed
     */
    public function getCommentMentions(){

        $response = $this->client->request('GET', "search?jql=(text%20~%20{$this->botName})%20AND%20updatedDate%20>%3D%20-7d%20ORDER%20BY%20updated%20DESC&fields=comment");

        if($response->getStatusCode() != 200){
            // log exception
        }

        return json_decode($response->getBody());
    }

    /**
     * Adds an issue comment
     * @param $jira_key
     * @param $comment
     *
     * @return array|mixed|object
     */
    public function leaveComment($jira_key, $comment){

        $response = null;

        try{
            $response = $this->client->request('POST', "issue/{$jira_key}/comment", [
                'json' => ['body' => $comment],
            ]);
        }catch(\Exception $e){
            $this->client->error(get_class($this), $e->getMessage());
            return null;
        }

        return json_decode($response->getBody());
    }
}