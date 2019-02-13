<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\MonitorCommand;

class ProcessMonitorCommands implements ShouldQueue
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
    public function handle()
    {
        $this->processCommands();
    }

    public function processCommands(){
        MonitorCommand::where('complete', 'false')->where('retries', '>', 0)->chunkById(50, function($commands){

            foreach ($commands as $command) {
                MonitorBuildCommand::dispatch($command);
            }

        });
    }
}
