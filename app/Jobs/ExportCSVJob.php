<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportCSVJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;

    // php artisan queue:work csv
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
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
        $fileName = now()->timestamp . "_users.csv";
        $fp = fopen('php://temp', 'w+');
        $headers = \Illuminate\Support\Facades\Schema::getColumnListing("users");
        fputcsv($fp, $headers);
        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);
        Storage::disk('local')->put($fileName, $csvContent);
        $chunkCount = 40000;
        $i = 0;
        DB::table('users')->orderBy('id')->chunk($chunkCount, function ($users) use ($fileName, $i) {
            info($i++);
            ProcessWriteCSVJob::dispatch(Storage::path($fileName), $users);
        });
    }
}
