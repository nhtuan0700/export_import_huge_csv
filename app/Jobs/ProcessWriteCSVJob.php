<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWriteCSVJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private $filePath, private $users)
    {
        $this->connection = 'database-csv';
        $this->queue = 'csv';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $handle = fopen($this->filePath, "a");
        foreach ($this->users as $user) {
            fputcsv($handle, (array) $user);
        }
        fclose($handle);
    }
}
