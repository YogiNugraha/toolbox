<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExcelToPdfService
{
    /**
     * Converts an Excel spreadsheet (.xlsx, .xls, .csv, .ods) to PDF using LibreOffice Calc engine.
     *
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function convert(UploadedFile $file): array
    {
        $pythonScript = base_path('storage/scripts/xlsx2pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script konversi Excel ke PDF tidak ditemukan di: {$pythonScript}");
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

        // Set timeout to 120 seconds for multi-sheet spreadsheets
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
            if (preg_match('/\{.*\}/s', $output, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (!$process->isSuccessful() || (isset($data['status']) && $data['status'] === 'error')) {
            $errorMsg = $data['message'] ?? $errorOutput ?? $output ?? 'Gagal memproses konversi Excel ke PDF.';
            throw new \Exception($errorMsg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath) || Storage::disk('local')->size($relativeResultPath) === 0) {
            throw new \Exception("File hasil dokumen PDF gagal dibuat atau kosong.");
        }

        $resultSize = Storage::disk('local')->size($relativeResultPath);

        return [
            'path' => $relativeResultPath,
            'result_path' => $relativeResultPath,
            'filename' => $outputFilename,
            'result_filename' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.pdf',
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.pdf',
            'original_size' => $file->getSize(),
            'result_size' => $resultSize,
            'new_size' => $resultSize,
            'extension' => 'pdf',
            'page_count' => $data['page_count'] ?? 1,
        ];
    }
}
