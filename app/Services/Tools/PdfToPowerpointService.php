<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfToPowerpointService
{
    /**
     * Converts a PDF file into PowerPoint presentation (.pptx).
     *
     * @param UploadedFile $file
     * @param int $dpi
     * @return array
     * @throws \Exception
     */
    public function convert(UploadedFile $file, int $dpi = 200): array
    {
        $pythonScript = base_path('storage/scripts/pdf2pptx.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script konversi PDF ke PowerPoint tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare temporary paths using local storage disk (app/private)
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'presentation_' . $uniqueId . '.pptx';
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
            (string) $dpi,
        ]);

        // Timeout 180 seconds for presentations with multiple slides
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
                throw new \Exception("Proses konversi PDF ke PowerPoint gagal: " . $decoded['message']);
            }
            throw new \Exception("Proses konversi gagal: " . ($errorOutput ?: 'Terjadi kesalahan internal.'));
        }

        $output = trim($process->getOutput());
        $resultData = json_decode($output, true);

        if (!$resultData || !isset($resultData['status']) || $resultData['status'] !== 'success') {
            $msg = $resultData['message'] ?? $output ?? 'Gagal memproses dokumen PDF.';
            throw new \Exception("Konversi gagal: " . $msg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath)) {
            throw new \Exception("File presentasi PowerPoint hasil konversi tidak ditemukan di server.");
        }

        $originalSize = $resultData['original_size'] ?? $file->getSize();
        $newSize = $resultData['new_size'] ?? Storage::disk('local')->size($relativeResultPath);
        $slideCount = $resultData['slide_count'] ?? 1;

        $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $downloadFilename = Str::slug($originalBase) . '.pptx';

        return [
            'success' => true,
            'result_path' => $relativeResultPath,
            'result_filename' => $downloadFilename,
            'original_size' => $originalSize,
            'new_size' => $newSize,
            'slide_count' => $slideCount,
        ];
    }
}
