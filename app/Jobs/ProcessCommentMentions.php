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

        $command_types = CommandType::all()->pluck('command');

        Comment::where('processed', false)->chunk(50, function($comments) use ($command_types){

            foreach ($comments as $comment) {
                $parsed_commands = $this->parseCommands($comment->body);
                $found_commands = $this->matchCommands($parsed_commands[1], $command_types);
                $this->insertCommands($comment->id, $found_commands);
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
    public function insertCommands($comment_id, $commands){
        foreach ($commands as $command){
            Command::updateOrCreate([
                'comment_id' => $comment_id,
                'command_type_id' => CommandType::where('command', $command)->firstOrFail()->id,
            ]);
        }
    }

    /**
     * @param $string
     *
     * @return array
     */
    function parseCommands($string){
        preg_match_all("/\/([\w-]+)/", $string, $matches);
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
        $intersect = $command_types->intersect($parsed_cmds);
        return $intersect->values()->toArray();
    }
}
