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
    protected $description = 'Clean up temporary files older than retention hours and mark activities as expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Storage::disk('local');
        $retentionHours = env('FILE_RETENTION_HOURS', 24);
        $threshold = now()->subHours($retentionHours);

        // Find activities older than threshold that still have a result_path
        $activities = \App\Models\Activity::whereNotNull('result_path')
            ->where('status', 'completed')
            ->where('updated_at', '<', $threshold)
            ->get();

        $deleted = 0;

        foreach ($activities as $activity) {
            if ($disk->exists($activity->result_path)) {
                $disk->delete($activity->result_path);
            }
            
            $activity->update([
                'status' => 'expired',
                'result_path' => null, // clear path so it's not downloadable
            ]);
            
            $deleted++;
        }
        
        // Also clean up any loose files in private/temp older than threshold
        $tempDir = 'private/temp';
        if ($disk->exists($tempDir)) {
            $files = $disk->files($tempDir);
            $now = now()->timestamp;
            foreach ($files as $file) {
                if ($now - $disk->lastModified($file) > ($retentionHours * 3600)) {
                    $disk->delete($file);
                }
            }
        }

        $this->info("Deleted and expired $deleted activity files.");
    }
}
