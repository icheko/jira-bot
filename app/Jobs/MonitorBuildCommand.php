<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\MonitorCommand;
use App\Services\Bamboo\BambooApi;
use App\Services\Jira\JiraApi;
use Log;

class MonitorBuildCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var MonitorCommand
     */
    private $monitor_command;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MonitorCommand $command)
    {
        $this->monitor_command = $command;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(BambooApi $bambooApi, JiraApi $jiraApi)
    {
        $this->log('Monitor job started');

        $result = $bambooApi->checkStatus($this->monitor_command->bamboo_build_key);

        if($result->finished){
            $comment = "Bamboo build job [{$bambooApi->getBrowseBuildLink($this->monitor_command->bamboo_build_key)}] complete, result [{$result->state}]";
            $this->log($comment);
            $jiraApi->leaveComment($this->monitor_command->command->comment->issue->jira_key, $comment);
            $this->monitor_command->complete = true;
            $this->monitor_command->save();
            return;
        }

        $this->monitor_command->retries -= 1;
        $this->monitor_command->save();
        $this->log("Bamboo build job not finished, {$result->progress->prettyTimeRemaining}");
    }

    /**
     * @param $text
     */
    private function log($text){
        $class = get_class($this);
        Log::info("[{$class}][{$this->monitor_command->id}]: {$text}");
    }
}
