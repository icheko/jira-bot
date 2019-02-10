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
use App\Models\Command;
use Log;

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
    private $jira_issue_key;

    /**
     * @var
     */
    private $bamboo_key;

    /**
     * @var
     */
    private $bitbucket_repo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Command $command, $flag)
    {
        $this->command_id       = $command->id;
        $this->flag             = $flag;
        $this->jira_issue_key   = $command->comment->issue->jira_key;
        $this->bamboo_key = $command->comment->issue->project->bamboo_key;
        $this->bitbucket_repo = $command->comment->issue->project->bitbucket_repo;
    }

    /**
     * Execute the job.
     * @param BitbucketServerApi $bsApi
     * @param BambooApi          $bambooApi
     * @param JiraApi            $jiraApi
     *
     * @throws \Exception
     */
    public function handle(BitbucketServerApi $bsApi, BambooApi $bambooApi, JiraApi $jiraApi)
    {

        if($this->flag == 'skip-tests')
            $this->log("Running build job, skipping tests");
        else
            $this->log("Running build job");

        $branches = $bsApi->getBranches($this->bitbucket_repo, $this->jira_issue_key);

        if($branches->size === 0){
            $jiraApi->leaveComment($this->jira_issue_key, "Ummm. I can't find any branch tagged with this jira key.");
            return;
        }

        $branch_name = $branches->values[0]->displayId;
        $foundBranches = $bambooApi->planBranchExists($this->bamboo_key, $branch_name);

        // add jira comment if multiple branches

        if($foundBranches->size >= 1){
            $this->log("Plan branch exists. Triggering the build.");

            $bambooApi->triggerPlanBuild($foundBranches->searchResults[0]->id, $this->flag == 'skip-tests');
            $jiraApi->leaveComment($this->jira_issue_key, "Woof. I have triggered the build job.");
            return;
        }

        if($bambooApi->createPlanBranch($this->bamboo_key, $branch_name))
            $jiraApi->leaveComment($this->jira_issue_key, "Woof. I have triggered the build job.");
    }

    /**
     * @param $text
     */
    private function log($text){
        $class = get_class($this);
        Log::info("[{$class}][{$this->command_id}][{$this->jira_issue_key}]: {$text}");
    }
}
