<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Jira\JiraApi;
use App\Models\Issue;
use App\Models\Comment;
use App\Models\CommandType;
use App\Models\Command;
use App\Models\Project;
use Log;
use Exception;

class ProcessCommentMentions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var JiraApi
     */
    private $api;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(JiraApi $api)
    {
        $this->api = $api;
        $this->loadComments();
        $this->processComments();
        $this->queueCommands();
    }

    /**
     * Call the API and load up @mention comments
     */
    public function loadComments(){

        $results = $this->api->getCommentMentions();

        foreach ($results->issues as $issue){
            $project_id = $this->findProjectId($issue->key);
            $issueModel = $this->insertIssue($issue, $project_id);
            $this->insertComments($issue->fields->comment->comments, $issueModel->id);

        }
    }

    /**
     * Loop through the unprocessed comments and
     * queue up commands
     */
    public function processComments(){

        $command_types = CommandType::all()->pluck('command_name');

        Comment::where('processed', 'false')->chunkById(50, function($comments) use ($command_types){

            foreach ($comments as $comment) {

                if($comment->issue->project->jira_key == 'UNK'){
                    $this->log("Comment [{$comment->id}] project is not setup - skip.");
                    $this->markProcessed($comment);
                    return;
                }

                $parsed_commands = $this->parseCommands($comment->body);
                $commands = $parsed_commands[1];
                $arguments = $parsed_commands[2];
                $matched_commands = $this->matchCommands($commands, $command_types);
                $this->insertCommands($comment->id, $matched_commands, $arguments);
                $this->markProcessed($comment);
            }
        });
    }

    /**
     * Queue up jobs to perform the commands
     */
    public function queueCommands(){
        Command::where('processed', 'false')->chunkById(50, function($commands) {

            foreach ($commands as $command) {
                $command_name = $command->commandType->command_name;
                $command_name = preg_replace("/[^A-Za-z]/", '', $command_name);
                $command_class = "App\\Jobs\\".ucfirst($command_name)."Command";

                if(!class_exists($command_class)){
                    throw new Exception("Command [{$command_name}] is not supported.");
                }

                $command_class::dispatch($command, $command->arguments);
                $this->markProcessed($command);
            }

        });
    }

    /**
     * @param $model
     */
    private function markProcessed($model){
        $model->processed = true;
        $model->save();
        $class = ucfirst($model->getTable());
        $this->log("[{$class}] with id [{$model->id}] marked processed");
    }

    /**
     * @param $issue_key
     *
     * @return mixed
     * @throws Exception
     */
    public function findProjectId($issue_key){
        $jira_key = explode('-', $issue_key);
        $jira_key = $jira_key[0];

        $project = Project::where('jira_key', $jira_key)->first();
        if(!$project)
            return Project::where('jira_key', 'UNK')->firstOrFail()->id;

        return $project->id;
    }

    /**
     * @param $issue
     *
     * @return mixed
     */
    public function insertIssue($issue, $project_id){
        return Issue::updateOrCreate([
                     'jira_id' => $issue->id,
                     'jira_key' => $issue->key,
                     'project_id' => $project_id,
                 ]);
    }

    /**
     * @param $comments
     * @param $issue_id
     */
    public function insertComments($comments, $issue_id){
        foreach ($comments as $comment){
            Comment::updateOrCreate([
                'issue_id' => $issue_id,
                'jira_comment_id' => $comment->id,
                'body' => $comment->body,
            ]);
        }
    }

    /**
     * @param $comment_id
     * @param $commands
     */
    public function insertCommands($comment_id, $commands, $arguments){
        foreach ($commands as $key => $command){
            $commandModel = Command::updateOrCreate([
                'comment_id' => $comment_id,
                'command_type_id' => CommandType::where('command_name', $command)->firstOrFail()->id,
            ]);
            if($arguments[$key]){
                $commandModel->arguments = $arguments[$key];
                $commandModel->save();
            }
        }
    }

    /**
     * @param $string
     *
     * @return array
     */
    function parseCommands($comment_body){
        preg_match_all("/\/([\w-]+):?([\w-]+)?/", $comment_body, $matches);
        return $matches;
    }

    /**
     * @param $parsed_cmds
     * @param $command_types
     *
     * @return array
     */
    function matchCommands($parsed_cmds, $command_types){
        $parsed_cmds = collect($parsed_cmds);
        $intersect = $parsed_cmds->intersect($command_types);
        return $intersect->toArray();
    }

    /**
     * @param $text
     */
    private function log($text){
        Log::info("{$text}");
    }

    /**
     * @param $text
     */
    private function error($text){
        Log::error("{$text}");
    }
}
