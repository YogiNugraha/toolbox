<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Tools\PdfConverterService;
use Illuminate\Support\Facades\Cache;

class ConvertPdfToWordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jobId;
    public $inputPath;
    public $outputDir;

    /**
     * Create a new job instance.
     */
    public function __construct(string $jobId, string $inputPath, string $outputDir)
    {
        $this->jobId = $jobId;
        $this->inputPath = $inputPath;
        $this->outputDir = $outputDir;
    }

    /**
     * Execute the job.
     */
    public function handle(PdfConverterService $converterService): void
    {
        try {
            Cache::put("pdf_conversion_status_{$this->jobId}", 'processing', 3600);

            $outputFile = $converterService->convertPdfToWord($this->inputPath, $this->outputDir);

            // Calculate sizes for stats
            $originalSize = filesize($this->inputPath);
            $newSize = filesize($outputFile);

            Cache::put("pdf_conversion_status_{$this->jobId}", 'completed', 3600);
            Cache::put("pdf_conversion_result_{$this->jobId}", [
                'path' => $outputFile,
                'original_size' => $originalSize,
                'new_size' => $newSize,
            ], 3600);

        } catch (\Exception $e) {
            Cache::put("pdf_conversion_status_{$this->jobId}", 'failed', 3600);
            Cache::put("pdf_conversion_error_{$this->jobId}", $e->getMessage(), 3600);
        }
    }
}
