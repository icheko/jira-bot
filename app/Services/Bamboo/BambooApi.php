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
            config('bot.bamboo.api_baseurl'), config('bot.bamboo.auth_username'), config('bot.bamboo.auth_password')
        );

        $this->botName = config('bot.bamboo.username');
    }

    /**
     * @param $branch
     *
     * @return string|string[]|null
     */
    public function cleanBranchName($branch){
        return preg_replace("/\//", '-', $branch);
    }

    /**
     * Create a plan branch
     * @param $bamboo_key
     * @param $branch
     *
     * @return array|mixed|object
     * @throws \Exception
     */
    public function createPlanBranch($bamboo_key, $branch){
        $branch_friendly = $this->cleanBranchName($branch);
        $this->log("Creating plan branch [{$branch_friendly}] in Bamboo");
        $response = null;

        try{
            $response = $this->client->request('PUT', "plan/{$bamboo_key}/branch/{$branch_friendly}?vcsBranch={$branch}&enabled=true&cleanupEnabled=true");
        } catch(\Exception $e){
            $this->client->error(get_class($this), $e->getMessage());
            throw $e;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Check if a plan branch exists for a branch in source control
     * @param $bamboo_key
     * @param $branch
     *
     * @return bool
     * @throws \Exception
     */
    public function planBranchExists($bamboo_key, $branch){
        $branch_friendly = $this->cleanBranchName($branch);
        $response = null;

        try{
            $response = $this->client->request('GET', "search/branches?masterPlanKey={$bamboo_key}&includeMasterBranch=true&searchTerm={$branch_friendly}");
        } catch(\Exception $e){
            $this->client->error(get_class($this), $e->getMessage());
            throw $e;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param $branch
     * @param $skip_tests
     *
     * @return array|mixed|object
     * @throws \Exception
     */
    public function triggerPlanBuild($branch, $skip_tests){
        $response = null;

        if($skip_tests)
            $skip_tests = '&bamboo.variable.skipTests=true';

        try{
            $response = $this->client->request('POST', "queue/{$branch}?stage&executeAllStages{$skip_tests}");
        } catch(\Exception $e){
            $this->client->error(get_class($this), $e->getMessage());
            throw $e;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param $text
     */
    private function log($text){
        Log::info("[BambooApi]: {$text}");
    }

    /**
     * @param $text
     */
    private function error($text){
        Log::error("[BambooApi]: {$text}");
    }
}