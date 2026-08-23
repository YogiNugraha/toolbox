<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfToExcelService
{
    /**
     * Converts a PDF document to an Excel Spreadsheet (.xlsx) by extracting tabular data.
     *
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function convert(UploadedFile $file): array
    {
        $pythonScript = base_path('storage/scripts/pdf2xlsx.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script konversi PDF ke Excel tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare temporary paths using local storage disk (app/private)
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'converted_' . $uniqueId . '.xlsx';
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

        // Set timeout to 120 seconds for multi-page documents
        $process->setTimeout(120);
        $process->run();

        // Cleanup temporary input if stored manually
        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        $output = trim($process->getOutput());
        $errorOutput = trim($process->getErrorOutput());

        // Parse JSON output from python script
        $data = null;
        if (!empty($output)) {
            // Find json string in output
            if (preg_match('/\{.*\}/s', $output, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (!$process->isSuccessful() || (isset($data['status']) && $data['status'] === 'error')) {
            $errorMsg = $data['message'] ?? $errorOutput ?? $output ?? 'Gagal memproses konversi PDF ke Excel.';
            throw new \Exception($errorMsg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath) || Storage::disk('local')->size($relativeResultPath) === 0) {
            throw new \Exception("File spreadsheet Excel gagal dibuat atau kosong.");
        }

        $resultSize = Storage::disk('local')->size($relativeResultPath);

        return [
            'path' => $relativeResultPath,
            'filename' => $outputFilename,
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.xlsx',
            'original_size' => $file->getSize(),
            'result_size' => $resultSize,
            'extension' => 'xlsx',
            'page_count' => $data['page_count'] ?? 1,
            'table_count' => $data['table_count'] ?? 0,
        ];
    }
}
