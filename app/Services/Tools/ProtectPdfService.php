<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProtectPdfService
{
    /**
     * Inspect PDF to determine page count and current encryption status.
     */
    public function inspect(UploadedFile $file): array
    {
        $pythonScript = base_path('storage/scripts/protect_pdf.py');
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
     * Encrypt PDF with user password and custom permission controls.
     */
    public function protect(
        UploadedFile $file,
        string $userPassword,
        ?string $ownerPassword = null,
        bool $allowPrint = true,
        bool $allowCopy = true
    ): array {
        $pythonScript = base_path('storage/scripts/protect_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Skrip proteksi PDF tidak ditemukan di: {$pythonScript}");
        }

        if (empty($userPassword)) {
            throw new \Exception("Password pembuka dokumen tidak boleh kosong.");
        }

        // 1. Prepare output target path in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'protected_' . $uniqueId . '.pdf';
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

        // 3. Command execution: python protect_pdf.py <input> <output> <user_pw> [owner_pw] [print] [copy]
        $cmd = [
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
            $userPassword,
            $ownerPassword ?: '-',
            $allowPrint ? '1' : '0',
            $allowCopy ? '1' : '0',
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
                return [
                    'result_path' => $relativeResultPath,
                    'result_filename' => $origBasename . '_protected.pdf',
                    'original_size' => $data['original_size'] ?? $file->getSize(),
                    'new_size' => $data['new_size'] ?? (file_exists($absoluteResultPath) ? filesize($absoluteResultPath) : 0),
                    'total_pages' => $data['total_pages'] ?? 1,
                ];
            }
            throw new \Exception($data['message'] ?? 'Gagal memproteksi dokumen PDF.');
        }

        throw new \Exception("Terjadi kesalahan saat memproteksi PDF: " . ($process->getErrorOutput() ?: $output));
    }
}
