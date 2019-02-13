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

class CommentMentions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $results = $api->getCommentMentions();

        foreach ($results->issues as $issue){

            $issueModel = Issue::updateOrCreate([
                        'issue_id' => $issue->id,
                        'issue_key' => $issue->key
                     ]);

            $comments = $issue->fields->comment->comments;

            foreach ($comments as $comment){

                $commentModel = Comment::updateOrCreate([
                                   'issue_id' => $issueModel->id,
                                   'comment_id' => $comment->id,
                                   'body' => $comment->body,
                                ]);
            }
        }
    }
}
