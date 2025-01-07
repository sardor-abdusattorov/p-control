<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLogs extends Command
{
    protected $signature = 'logs:clear';
    protected $description = 'Clear Laravel log files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $logFiles = File::files(storage_path('logs'));

        foreach ($logFiles as $file) {
            File::delete($file);
        }

        $this->info('Log files cleared successfully!');
    }
}
