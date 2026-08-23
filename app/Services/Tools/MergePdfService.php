<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MergePdfService
{
    /**
     * Merges multiple PDF files into one unified PDF document.
     *
     * @param array<UploadedFile> $files
     * @return array
     * @throws \Exception
     */
    public function merge(array $files): array
    {
        if (count($files) < 2) {
            throw new \Exception("Diperlukan minimal 2 file PDF untuk digabungkan.");
        }

        $pythonScript = base_path('storage/scripts/merge_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script penggabung PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare target paths
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'merged_' . $uniqueId . '.pdf';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        $absoluteResultPath = Storage::disk('local')->path($relativeResultPath);

        // 2. Prepare all input file paths
        $tempStoredPaths = [];
        $realInputPaths = [];
        $totalOriginalSize = 0;

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $totalOriginalSize += $file->getSize();
            $realPath = $file->getRealPath();

            if (!$realPath || !file_exists($realPath)) {
                $tempRel = $file->store('temp_tools', 'local');
                $tempStoredPaths[] = $tempRel;
                $realInputPaths[] = Storage::disk('local')->path($tempRel);
            } else {
                $realInputPaths[] = $realPath;
            }
        }

        if (count($realInputPaths) < 2) {
            throw new \Exception("Gagal memproses file PDF input untuk digabungkan.");
        }

        // 3. Command execution: python merge_pdf.py <output_pdf> <input_1> <input_2> ...
        $cmd = array_merge([$pythonExecutable, $pythonScript, $absoluteResultPath], $realInputPaths);

        $process = new Process($cmd);
        $process->setTimeout(120);
        $process->run();

        // Cleanup temporary stored files
        foreach ($tempStoredPaths as $tempPath) {
            Storage::disk('local')->delete($tempPath);
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
            $errorMsg = $data['message'] ?? $errorOutput ?? $output ?? 'Gagal menggabungkan file PDF.';
            throw new \Exception($errorMsg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath) || Storage::disk('local')->size($relativeResultPath) === 0) {
            throw new \Exception("File hasil PDF gabungan gagal dibuat atau kosong.");
        }

        $resultSize = Storage::disk('local')->size($relativeResultPath);

        return [
            'path' => $relativeResultPath,
            'result_path' => $relativeResultPath,
            'filename' => $outputFilename,
            'result_filename' => 'dokumen_gabungan_' . date('Ymd_His') . '.pdf',
            'original_size' => $totalOriginalSize,
            'result_size' => $resultSize,
            'new_size' => $resultSize,
            'files_count' => count($realInputPaths),
            'page_count' => $data['page_count'] ?? 0,
            'extension' => 'pdf',
        ];
    }
}
