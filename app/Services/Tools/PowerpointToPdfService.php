<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PowerpointToPdfService
{
    /**
     * Converts a PowerPoint presentation (.pptx, .ppt, .odp, .ppsx) to PDF using LibreOffice Impress engine.
     *
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function convert(UploadedFile $file): array
    {
        $pythonScript = base_path('storage/scripts/pptx2pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script konversi PowerPoint ke PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare temporary paths using local storage disk (app/private)
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'converted_' . $uniqueId . '.pdf';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        // Ensure target directory exists on local disk
        Storage::disk('local')->makeDirectory($relativeDir);
        $absoluteResultPath = Storage::disk('local')->path($relativeResultPath);

        // 2. Obtain real absolute path of uploaded temporary file
        $realInputPath = $file->getRealPath();
        if (!$realInputPath || !file_exists($realInputPath)) {
            $tempRelative = $file->store('temp_tools', 'local');
            $realInputPath = Storage::disk('local')->path($tempRelative);
            $cleanupInput = true;
        } else {
            $cleanupInput = false;
        }

        // 3. Execute python script via Symfony Process
        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
        ]);

        // Set timeout to 180 seconds for large multi-slide presentations
        $process->setTimeout(180);
        $process->run();

        // Cleanup temporary input if stored manually
        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput() ?: $process->getOutput();
            $decoded = json_decode($errorOutput, true);
            if ($decoded && isset($decoded['message'])) {
                throw new \Exception("Proses konversi PowerPoint ke PDF gagal: " . $decoded['message']);
            }
            throw new \Exception("Proses konversi gagal: " . ($errorOutput ?: 'Terjadi kesalahan internal.'));
        }

        $output = trim($process->getOutput());
        $resultData = json_decode($output, true);

        if (!$resultData || !isset($resultData['status']) || $resultData['status'] !== 'success') {
            $msg = $resultData['message'] ?? $output ?? 'Gagal memproses file PowerPoint.';
            throw new \Exception("Konversi gagal: " . $msg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath)) {
            throw new \Exception("File PDF hasil konversi tidak ditemukan di server.");
        }

        $originalSize = $resultData['original_size'] ?? $file->getSize();
        $newSize = $resultData['new_size'] ?? Storage::disk('local')->size($relativeResultPath);
        $pageCount = $resultData['page_count'] ?? 1;

        $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $downloadFilename = Str::slug($originalBase) . '.pdf';

        return [
            'success' => true,
            'result_path' => $relativeResultPath,
            'result_filename' => $downloadFilename,
            'original_size' => $originalSize,
            'new_size' => $newSize,
            'page_count' => $pageCount,
        ];
    }
}
