<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SplitPdfService
{
    /**
     * Get total page count of a PDF file using split_pdf.py.
     */
    public function getPageCount(UploadedFile $file): int
    {
        $pythonScript = base_path('storage/scripts/split_pdf.py');
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
     * Splits a PDF file based on mode ('all' or 'range') and custom page range string.
     *
     * @param UploadedFile $file
     * @param string $mode 'all' or 'range'
     * @param string $pageRange e.g. '1-3, 5'
     * @return array
     * @throws \Exception
     */
    public function split(UploadedFile $file, string $mode = 'all', string $pageRange = ''): array
    {
        $pythonScript = base_path('storage/scripts/split_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script pemisah PDF tidak ditemukan di: {$pythonScript}");
        }

        $isZip = ($mode === 'all');
        $extension = $isZip ? 'zip' : 'pdf';

        // 1. Prepare output target path in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'split_' . $uniqueId . '.' . $extension;
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

        // 3. Command execution: python split_pdf.py <input> <output> <mode> [page_range]
        $cmd = [
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
            $mode,
            $pageRange ?: ''
        ];

        $process = new Process($cmd);
        $process->setTimeout(120);
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
            $errorMsg = $data['message'] ?? $errorOutput ?? $output ?? 'Gagal memisahkan dokumen PDF.';
            throw new \Exception($errorMsg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath) || Storage::disk('local')->size($relativeResultPath) === 0) {
            throw new \Exception("File hasil pemisahan PDF gagal dibuat atau kosong.");
        }

        $baseOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $downloadName = $isZip ? ($baseOriginalName . '_halaman_terpisah.zip') : ($baseOriginalName . '_ekstrak_halaman.pdf');

        $resultSize = Storage::disk('local')->size($relativeResultPath);

        return [
            'path' => $relativeResultPath,
            'result_path' => $relativeResultPath,
            'filename' => $outputFilename,
            'result_filename' => $downloadName,
            'original_size' => $file->getSize(),
            'result_size' => $resultSize,
            'new_size' => $resultSize,
            'mode' => $mode,
            'original_pages' => $data['original_pages'] ?? 1,
            'extracted_pages' => $data['extracted_pages'] ?? 1,
            'extension' => $extension,
        ];
    }
}
