<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WatermarkPdfService
{
    /**
     * Inspect PDF to determine page count and current encryption status.
     */
    public function inspect(UploadedFile $file): array
    {
        $pythonScript = base_path('storage/scripts/watermark_pdf.py');
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
     * Apply text or image watermark to PDF pages.
     */
    public function watermark(UploadedFile $file, array $config, ?UploadedFile $watermarkImage = null): array
    {
        $pythonScript = base_path('storage/scripts/watermark_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Skrip watermark PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare output target path in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'watermarked_' . $uniqueId . '.pdf';
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

        // 3. Prepare watermark image if type is image
        $cleanupImage = false;
        if (($config['type'] ?? 'text') === 'image' && $watermarkImage) {
            $imgRealPath = $watermarkImage->getRealPath();
            if (!$imgRealPath || !file_exists($imgRealPath)) {
                $tempImgRelative = $watermarkImage->store('temp_tools', 'local');
                $imgRealPath = Storage::disk('local')->path($tempImgRelative);
                $cleanupImage = true;
            }
            $config['image_path'] = $imgRealPath;
        }

        // 4. Command execution: python watermark_pdf.py <input> <output> <config_json>
        $configJson = json_encode($config);
        $cmd = [
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
            $configJson,
        ];

        $process = new Process($cmd);
        $process->setTimeout(180);
        $process->run();

        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        if ($cleanupImage && isset($tempImgRelative)) {
            Storage::disk('local')->delete($tempImgRelative);
        }

        $output = trim($process->getOutput());
        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            if (isset($data['status']) && $data['status'] === 'success') {
                $origBasename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                return [
                    'result_path' => $relativeResultPath,
                    'result_filename' => $origBasename . '_watermarked.pdf',
                    'original_size' => $data['original_size'] ?? $file->getSize(),
                    'new_size' => $data['new_size'] ?? (file_exists($absoluteResultPath) ? filesize($absoluteResultPath) : 0),
                    'total_pages' => $data['total_pages'] ?? 1,
                    'watermarked_pages' => $data['watermarked_pages'] ?? 1,
                ];
            }
            throw new \Exception($data['message'] ?? 'Gagal menambahkan watermark pada dokumen.');
        }

        throw new \Exception("Terjadi kesalahan saat memproses watermark PDF: " . ($process->getErrorOutput() ?: $output));
    }
}
