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

class BuildCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var
     */
    private $command_id;

    /**
     * @var
     */
    private $flag;

    /**
     * @var
     */
    private $jira_key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($command_id, $flag, $jira_key)
    {
        $this->command_id = $command_id;
        $this->flag = $flag;
        $this->jira_key = $jira_key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(BitbucketServerApi $bsApi, BambooApi $bambooApi, JiraApi $jiraApi)
    {

        if($this->flag == 'skip-tests')
            $this->log("Running build job, skipping tests");
        else
            $this->log("Running build job");

        $branches = $bsApi->getBranches($this->jira_key);

        if($branches->size === 0){
            $jiraApi->leaveComment($this->jira_key, "Ummm. I can't find any branch tagged with this jira key.");
            return;
        }

        $branch_name = $branches->values[0]->displayId;
        $foundBranches = $bambooApi->planBranchExists($branch_name);

        // add jira comment if multiple branches

        if($foundBranches->size == 1){
            $this->log("Plan branch exists. Triggering the build.");

            $bambooApi->triggerPlanBuild($foundBranches->searchResults[0]->id, $this->flag == 'skip-tests');
            $jiraApi->leaveComment($this->jira_key, "Woof. I have triggered the build job.");
            return;
        }

        if($bambooApi->createPlanBranch($branch_name))
            $jiraApi->leaveComment($this->jira_key, "Woof. I have triggered the build job.");
    }

    /**
     * @param $text
     */
    private function log($text){
        Log::info("[{$this->command_id}][{$this->jira_key}]: {$text}");
    }
}
