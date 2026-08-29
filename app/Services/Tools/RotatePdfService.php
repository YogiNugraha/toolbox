<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RotatePdfService
{
    /**
     * Get total page count of a PDF file using rotate_pdf.py.
     */
    public function getPageCount(UploadedFile $file): int
    {
        $pythonScript = base_path('storage/scripts/rotate_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            return 1;
        }

        $process = new Process([
            $pythonExecutable,
            $pythonScript,
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
     * Render fast lightweight thumbnails for all pages of the PDF.
     */
    public function renderThumbnails(UploadedFile $file, int $dpi = 60): array
    {
        $pythonScript = base_path('storage/scripts/rotate_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            throw new \Exception("File PDF tidak ditemukan.");
        }

        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            $realPath,
            '--render-thumbnails',
            (string)$dpi
        ]);
        $process->setTimeout(60);
        $process->run();

        $output = trim($process->getOutput());
        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            if (isset($data['status']) && $data['status'] === 'success') {
                return $data;
            }
        }

        throw new \Exception("Gagal me-render thumbnail PDF: " . ($process->getErrorOutput() ?: $output));
    }

    /**
     * Rotates PDF pages and saves to local storage.
     *
     * @param UploadedFile $file
     * @param array $rotations Array of ['page_num' => rotation_angle]
     * @param int $globalRotation Global rotation angle for all pages
     * @return array
     * @throws \Exception
     */
    public function rotate(UploadedFile $file, array $rotations = [], int $globalRotation = 0): array
    {
        $pythonScript = base_path('storage/scripts/rotate_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script pemutar PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare output target path in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'rotated_' . $uniqueId . '.pdf';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        $absoluteResultPath = Storage::disk('local')->path($relativeResultPath);

        // 2. Prepare uploaded input file
        $realInputPath = $file->getRealPath();
        $cleanupInput = false;
        if (!$realInputPath || !file_exists($realInputPath)) {
            $tempRelative = $file->store('temp_tools', 'local');
            $realInputPath = Storage::disk('local')->path($tempRelative);
            $cleanupInput = true;
        }

        // 3. Command execution: python rotate_pdf.py <input> <output> <rotations_json> [global_angle]
        $rotationsJson = json_encode($rotations);
        $cmd = [
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
            $rotationsJson,
            (string)$globalRotation
        ];

        $process = new Process($cmd);
        $process->setTimeout(120);
        $process->run();

        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        $output = trim($process->getOutput());

        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            if (isset($data['status']) && $data['status'] === 'success') {
                $origBasename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $finalFilename = $origBasename . '_rotated.pdf';

                return [
                    'result_path' => $relativeResultPath,
                    'result_filename' => $finalFilename,
                    'original_size' => $data['original_size'] ?? $file->getSize(),
                    'new_size' => $data['new_size'] ?? (file_exists($absoluteResultPath) ? filesize($absoluteResultPath) : 0),
                    'total_pages' => $data['total_pages'] ?? 1,
                    'rotated_pages' => $data['rotated_pages'] ?? 0,
                    'extension' => 'pdf'
                ];
            }

            throw new \Exception($data['message'] ?? 'Gagal memutar dokumen PDF.');
        }

        throw new \Exception("Terjadi kesalahan saat memproses dokumen PDF: " . ($process->getErrorOutput() ?: $output));
    }
}
