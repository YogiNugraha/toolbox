<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfToJpgService
{
    /**
     * Get total page count of a PDF file using pdf2jpg.py.
     */
    public function getPageCount(UploadedFile $file): int
    {
        $pythonScript = base_path('storage/scripts/pdf2jpg.py');
        $pythonExecutable = config('services.python_path', 'python');

        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            return 1;
        }

        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            $realPath,
            $realPath,
            '--count'
        ]);
        $process->setTimeout(30);
        $process->run();

        $output = trim($process->getOutput());
        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            return $data['page_count'] ?? 1;
        }

        return 1;
    }

    /**
     * Converts a PDF file into JPG images (direct JPG for 1-page, ZIP for multi-page).
     *
     * @param UploadedFile $file
     * @param string $quality 'standard' (150 DPI) or 'hd' (300 DPI)
     * @return array
     * @throws \Exception
     */
    public function convert(UploadedFile $file, string $quality = 'standard'): array
    {
        $pythonScript = base_path('storage/scripts/pdf2jpg.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script konversi PDF ke JPG tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare temporary & target paths in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        
        // We'll prepare a placeholder output path (extension will be adjusted if needed)
        $outputFilename = 'converted_' . $uniqueId . '.tmp';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        $absoluteResultPath = Storage::disk('local')->path($relativeResultPath);

        // 2. Prepare uploaded input file
        $realInputPath = $file->getRealPath();
        if (!$realInputPath || !file_exists($realInputPath)) {
            $tempRelative = $file->store('temp_tools', 'local');
            $realInputPath = Storage::disk('local')->path($tempRelative);
            $cleanupInput = true;
        } else {
            $cleanupInput = false;
        }

        // 3. Command execution: python pdf2jpg.py <input> <output> <quality>
        $cmd = [
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
            $quality
        ];

        $process = new Process($cmd);
        $process->setTimeout(180);
        $process->run();

        // Cleanup temporary input
        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        $output = trim($process->getOutput());
        $errorOutput = trim($process->getErrorOutput());

        $data = null;
        if (!empty($output)) {
            if (preg_match('/\{.*\}/s', $output, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (!$process->isSuccessful() || (isset($data['status']) && $data['status'] === 'error')) {
            $errorMsg = $data['message'] ?? $errorOutput ?? $output ?? 'Gagal mengonversi file PDF ke JPG.';
            throw new \Exception($errorMsg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath) || Storage::disk('local')->size($relativeResultPath) === 0) {
            throw new \Exception("File hasil konversi JPG gagal dibuat atau kosong.");
        }

        $extension = $data['extension'] ?? ($data['format'] ?? 'jpg');
        $finalFilename = 'converted_' . $uniqueId . '.' . $extension;
        $finalRelativePath = $relativeDir . '/' . $finalFilename;

        // Rename from .tmp to final extension
        Storage::disk('local')->move($relativeResultPath, $finalRelativePath);

        $baseOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $downloadName = ($extension === 'zip') 
            ? ($baseOriginalName . '_gambar_jpg.zip') 
            : ($baseOriginalName . '.jpg');

        $resultSize = Storage::disk('local')->size($finalRelativePath);

        return [
            'path' => $finalRelativePath,
            'result_path' => $finalRelativePath,
            'filename' => $finalFilename,
            'result_filename' => $downloadName,
            'original_size' => $file->getSize(),
            'result_size' => $resultSize,
            'new_size' => $resultSize,
            'page_count' => $data['page_count'] ?? 1,
            'quality' => $quality,
            'extension' => $extension,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
        ];
    }
}
