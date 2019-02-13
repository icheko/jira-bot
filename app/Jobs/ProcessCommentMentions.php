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

            $issueModel = $this->insertIssue($issue);
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
                $parsed_commands = $this->parseCommands($comment->body);
                $commands = $parsed_commands[1];
                $arguments = $parsed_commands[2];
                $matched_commands = $this->matchCommands($commands, $command_types);
                $this->insertCommands($comment->id, $matched_commands, $arguments);
                // mark processed
                $comment->processed = true;
                $comment->save();
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
                    throw new \Exception("Command [{$command_name}] is not supported.");
                }

                $command_class::dispatch($command->arguments, $command->comment->issue->jira_key);

                // mark processed
                $command->processed = true;
                $command->save();
                //call_user_func($command_class.'::dispatch', [$command->arguments]);
            }

        });
    }

    /**
     * @param $issue
     *
     * @return mixed
     */
    public function insertIssue($issue){
        return Issue::updateOrCreate([
                     'jira_id' => $issue->id,
                     'jira_key' => $issue->key
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
        preg_match_all("/\/([\w-]+):?(\w+)?/", $comment_body, $matches);
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
}
