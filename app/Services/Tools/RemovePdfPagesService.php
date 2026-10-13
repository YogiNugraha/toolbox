<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RemovePdfPagesService
{
    /**
     * Inspect PDF to determine page count, encryption, and size.
     */
    public function inspect(UploadedFile $file): array
    {
        $pythonScript = base_path('storage/scripts/remove_pdf_pages.py');
        $pythonExecutable = config('services.python_path', 'python');

        $realPath = $file->getRealPath();
        $cleanup = false;
        if (!$realPath || !file_exists($realPath)) {
            $temp = $file->store('temp_tools', 'local');
            $realPath = Storage::disk('local')->path($temp);
            $cleanup = true;
        }

        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            '--inspect',
            $realPath
        ]);
        $process->setTimeout(30);
        $process->run();

        if ($cleanup && isset($temp)) {
            Storage::disk('local')->delete($temp);
        }

        $output = trim($process->getOutput());
        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            if (isset($data['status']) && $data['status'] === 'success') {
                return $data;
            }
        }

        return [
            'is_encrypted' => false,
            'needs_pass' => false,
            'total_pages' => 1,
            'file_size' => $file->getSize(),
        ];
    }

    /**
     * Generate visual page thumbnails encoded as base64 data URIs.
     */
    public function generateThumbnails(UploadedFile $file, int $maxPages = 36): array
    {
        $pythonScript = base_path('storage/scripts/remove_pdf_pages.py');
        $pythonExecutable = config('services.python_path', 'python');

        $realInputPath = $file->getRealPath();
        $cleanupInput = false;
        if (!$realInputPath || !file_exists($realInputPath)) {
            $tempRelative = $file->store('temp_tools', 'local');
            $realInputPath = Storage::disk('local')->path($tempRelative);
            $cleanupInput = true;
        }

        $batchId = Str::uuid()->toString();
        $relativeDir = 'temp_thumbs/' . $batchId;
        $absOutputDir = Storage::disk('public')->path($relativeDir);
        Storage::disk('public')->makeDirectory($relativeDir);

        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            '--thumbnails',
            $realInputPath,
            $absOutputDir,
            (string) $maxPages
        ]);
        $process->setTimeout(45);
        $process->run();

        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        $count = 0;
        $output = trim($process->getOutput());

        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            if (isset($data['rendered_count'])) {
                $count = (int) $data['rendered_count'];
            }
        }

        return [
            'batch_id' => $batchId,
            'count' => $count,
        ];
    }

    /**
     * Clean up thumbnail directory when component resets.
     */
    public function cleanupThumbnails(?string $batchId): void
    {
        if ($batchId) {
            Storage::disk('public')->deleteDirectory('temp_thumbs/' . $batchId);
        }
    }

    /**
     * Remove selected pages and save the cleaned PDF document.
     */
    public function removePages(UploadedFile $file, string $pagesToRemoveStr): array
    {
        $pythonScript = base_path('storage/scripts/remove_pdf_pages.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Skrip penghapus halaman PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare output target path in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'removed_' . $uniqueId . '.pdf';
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

        // 3. Command execution: python remove_pdf_pages.py <input> <output> <pages_to_remove>
        $cmd = [
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
            $pagesToRemoveStr,
        ];

        $process = new Process($cmd);
        $process->setTimeout(180);
        $process->run();

        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        $output = trim($process->getOutput());
        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            if (isset($data['status']) && $data['status'] === 'success') {
                $origBasename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                return [
                    'result_path' => $relativeResultPath,
                    'result_filename' => $origBasename . '_pruned.pdf',
                    'original_size' => $data['original_size'] ?? $file->getSize(),
                    'new_size' => $data['new_size'] ?? (file_exists($absoluteResultPath) ? filesize($absoluteResultPath) : 0),
                    'total_pages_before' => $data['total_pages_before'] ?? 1,
                    'total_pages_after' => $data['total_pages_after'] ?? 1,
                    'removed_count' => $data['removed_count'] ?? 1,
                    'removed_pages' => $data['removed_pages'] ?? [],
                ];
            }
            throw new \Exception($data['message'] ?? 'Gagal menghapus halaman dokumen PDF.');
        }

        throw new \Exception("Terjadi kesalahan sistem saat memproses penghapusan halaman: " . ($process->getErrorOutput() ?: $output));
    }
}
