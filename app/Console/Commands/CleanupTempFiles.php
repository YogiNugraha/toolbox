<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTempFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary files older than 1 hour in storage/app/private/temp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Storage::disk('local');
        $directory = 'private/temp';

        if (!$disk->exists($directory)) {
            $this->info("Directory does not exist. Nothing to clean.");
            return;
        }

        $files = $disk->files($directory);
        $now = now()->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);
            
            // Delete if older than 1 hour (3600 seconds)
            if ($now - $lastModified > 3600) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Deleted $deleted temporary files.");
    }
}
