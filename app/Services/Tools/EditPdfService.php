<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditPdfService
{
    /**
     * Get total page count of a PDF file using edit_pdf.py.
     */
    public function getPageCount(UploadedFile $file): int
    {
        $pythonScript = base_path('storage/scripts/edit_pdf.py');
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
     * Renders a specific page of a PDF file to a base64 image data URL.
     */
    public function renderPage(UploadedFile $file, int $pageNum = 1, int $dpi = 130): array
    {
        $pythonScript = base_path('storage/scripts/edit_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            throw new \Exception("File PDF tidak ditemukan.");
        }

        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            $realPath,
            '--render-page',
            (string)$pageNum,
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

        throw new \Exception("Gagal merender halaman PDF ke gambar: " . ($process->getErrorOutput() ?: $output));
    }

    /**
     * Edits a PDF file with visual canvas modifications, rotation, page ordering, and metadata.
     *
     * @param UploadedFile $file
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function edit(UploadedFile $file, array $options = []): array
    {
        $pythonScript = base_path('storage/scripts/edit_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script pengedit PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare target paths in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'edited_' . $uniqueId . '.pdf';
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

        // 3. Save options to a temporary JSON file (prevents CLI argument size limits on Windows)
        $optionsTempRel = 'temp_tools/edit_options_' . $uniqueId . '.json';
        Storage::disk('local')->put($optionsTempRel, json_encode($options, JSON_UNESCAPED_UNICODE));
        $absoluteOptionsPath = Storage::disk('local')->path($optionsTempRel);

        // 4. Command execution: python edit_pdf.py <input> <output> <options_file.json>
        $cmd = [
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
            $absoluteOptionsPath
        ];

        $process = new Process($cmd);
        $process->setTimeout(180);
        $process->run();

        // Cleanup temporary files
        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }
        Storage::disk('local')->delete($optionsTempRel);

        $output = trim($process->getOutput());
        $errorOutput = trim($process->getErrorOutput());

        $data = null;
        if (!empty($output)) {
            if (preg_match('/\{.*\}/s', $output, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (!$process->isSuccessful() || (isset($data['status']) && $data['status'] === 'error')) {
            $errorMsg = $data['message'] ?? $errorOutput ?? $output ?? 'Gagal mengedit dokumen PDF.';
            throw new \Exception($errorMsg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath) || Storage::disk('local')->size($relativeResultPath) === 0) {
            throw new \Exception("File PDF hasil edit gagal dibuat atau kosong.");
        }

        $baseOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $downloadName = $baseOriginalName . '_edited.pdf';

        $resultSize = Storage::disk('local')->size($relativeResultPath);

        return [
            'path' => $relativeResultPath,
            'result_path' => $relativeResultPath,
            'filename' => $outputFilename,
            'result_filename' => $downloadName,
            'original_size' => $file->getSize(),
            'result_size' => $resultSize,
            'new_size' => $resultSize,
            'original_pages' => $data['original_pages'] ?? 1,
            'final_pages' => $data['final_pages'] ?? 1,
            'extension' => 'pdf',
        ];
    }
}
