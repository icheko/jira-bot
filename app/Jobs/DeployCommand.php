<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\BitbucketServer\BitbucketServerApi;
use App\Services\Bamboo\BambooApi;
use App\Services\Jira\JiraApi;
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
    public function handle(BitbucketServerApi $bsApi, BambooApi $bambooApi, JiraApi $jiraApi)
    {
        $this->log("Running a [{$this->env}] deployment");

        $branches = $bsApi->getBranches($this->jira_key);

        if($branches->size === 0){
            $jiraApi->leaveComment($this->jira_key, "Ummm. I can't find any branch tagged with this jira key.");
            return;
        }

        // add jira comment if no branches
        // add jira comment if multiple branches

        $branch_name = $branches->values[0]->displayId;
        $result = $bambooApi->createPlanBranch($branch_name);

        if($result){
            $bamboo_link = $result->link->href;
            $jiraApi->leaveComment($this->jira_key, "Woof. I have triggered the build job.");
        }
    }

    /**
     * @param $text
     */
    private function log($text){
        Log::info("[{$this->jira_key}]: {$text}");
    }
}
