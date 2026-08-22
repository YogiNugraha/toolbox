<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfCompressorService
{
    /**
     * Compresses a PDF file using PyMuPDF backend script.
     *
     * @param UploadedFile $file
     * @param string $preset 'extreme', 'recommended', 'low', or 'custom'
     * @param array $customOptions
     * @return array
     * @throws \Exception
     */
    public function compress(UploadedFile $file, string $preset = 'recommended', array $customOptions = []): array
    {
        $pythonScript = base_path('storage/scripts/compress_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script kompresi PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Determine compression parameters based on preset
        $dpi = 150;
        $quality = 75;
        $stripMetadata = 0;

        if ($preset === 'extreme') {
            $dpi = 72;
            $quality = 50;
            $stripMetadata = 1;
        } elseif ($preset === 'recommended') {
            $dpi = 150;
            $quality = 75;
            $stripMetadata = 0;
        } elseif ($preset === 'low') {
            $dpi = 200;
            $quality = 85;
            $stripMetadata = 0;
        } elseif ($preset === 'custom') {
            $dpi = isset($customOptions['dpi']) ? (int) $customOptions['dpi'] : 150;
            $quality = isset($customOptions['quality']) ? (int) $customOptions['quality'] : 75;
            $stripMetadata = !empty($customOptions['strip_metadata']) ? 1 : 0;
        }

        // 2. Prepare temporary paths using local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'compressed_' . $uniqueId . '.pdf';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        $absoluteOutputDir = Storage::disk('local')->path($relativeDir);
        if (!file_exists($absoluteOutputDir)) {
            mkdir($absoluteOutputDir, 0755, true);
        }

        $inputPath = $file->getRealPath();
        $outputPath = Storage::disk('local')->path($relativeResultPath);

        // 3. Execute Python process
        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            $inputPath,
            $outputPath,
            (string) $dpi,
            (string) $quality,
            (string) $stripMetadata,
        ]);

        $process->setTimeout(180); // Up to 3 minutes for large documents

        try {
            $process->mustRun();
            $output = trim($process->getOutput());
            $data = json_decode($output, true);

            if (!$data || !isset($data['status']) || $data['status'] !== 'success') {
                $errorMsg = $data['message'] ?? $output ?? 'Gagal memproses file PDF.';
                throw new \Exception($errorMsg);
            }

            if (!file_exists($outputPath)) {
                throw new \Exception("File hasil kompresi tidak ditemukan di server.");
            }

            return [
                'path' => $relativeResultPath,
                'original_size' => $data['original_size'] ?? $file->getSize(),
                'new_size' => $data['compressed_size'] ?? filesize($outputPath),
                'savings_bytes' => $data['savings_bytes'] ?? 0,
                'savings_percentage' => $data['savings_percentage'] ?? 0.0,
                'page_count' => $data['page_count'] ?? 1,
                'extension' => 'pdf',
                'filename' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_compressed.pdf',
            ];

        } catch (ProcessFailedException $e) {
            throw new \Exception("Proses kompresi PDF gagal: " . $e->getMessage());
        }
    }
}
