<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\BitbucketServer\BitbucketServerApi;
use App\Services\Bamboo\BambooApi;
use \Log;

class DeployCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $env;

    private $jira_key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($environment, $jira_key)
    {
        $this->env = $environment;
        $this->jira_key = $jira_key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(BitbucketServerApi $bsApi, BambooApi $bambooApi)
    {
        $this->log("Running a [{$this->env}] deployment");

        $branches = $bsApi->getBranches($this->jira_key);


        if($branches->size === 0)
            return;

        // add jira comment if no branches
        // add jira comment if multiple branches

        $branch_name = $branches->values[0]->displayId;
        $result = $bambooApi->createPlanBranch($branch_name);
        print_r($result);
    }

    /**
     * @param $text
     */
    private function log($text){
        Log::info("[{$this->jira_key}]: {$text}");
    }
}
