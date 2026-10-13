<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageNumberPdfService
{
    /**
     * Inspect PDF to determine page count, encryption, and size.
     */
    public function inspect(UploadedFile $file): array
    {
        $pythonScript = base_path('storage/scripts/page_number_pdf.py');
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
     * Render a fast thumbnail preview of a numbered page as base64 data URI.
     */
    public function renderPreview(UploadedFile $file, array $options, int $pageIdx = 1): ?string
    {
        $pythonScript = base_path('storage/scripts/page_number_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        $realInputPath = $file->getRealPath();
        $cleanupInput = false;
        if (!$realInputPath || !file_exists($realInputPath)) {
            $tempRelative = $file->store('temp_tools', 'local');
            $realInputPath = Storage::disk('local')->path($tempRelative);
            $cleanupInput = true;
        }

        $tempPreview = 'temp_tools/preview_' . Str::uuid()->toString() . '.png';
        $absPreview = Storage::disk('local')->path($tempPreview);
        Storage::disk('local')->makeDirectory('temp_tools');

        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            '--preview',
            $realInputPath,
            $absPreview,
            json_encode($options),
            (string) $pageIdx
        ]);
        $process->setTimeout(30);
        $process->run();

        if ($cleanupInput && isset($tempRelative)) {
            Storage::disk('local')->delete($tempRelative);
        }

        if (file_exists($absPreview)) {
            $bytes = file_get_contents($absPreview);
            @unlink($absPreview);
            if ($bytes) {
                return 'data:image/png;base64,' . base64_encode($bytes);
            }
        }

        return null;
    }

    /**
     * Add page numbers to all or targeted pages of the PDF document.
     */
    public function paginate(UploadedFile $file, array $options): array
    {
        $pythonScript = base_path('storage/scripts/page_number_pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Skrip nomor halaman PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare output target path in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'numbered_' . $uniqueId . '.pdf';
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

        // 3. Command execution: python page_number_pdf.py <input> <output> <options_json>
        $optionsJson = json_encode($options);
        $cmd = [
            $pythonExecutable,
            $pythonScript,
            $realInputPath,
            $absoluteResultPath,
            $optionsJson,
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
                    'result_filename' => $origBasename . '_numbered.pdf',
                    'original_size' => $data['original_size'] ?? $file->getSize(),
                    'new_size' => $data['new_size'] ?? (file_exists($absoluteResultPath) ? filesize($absoluteResultPath) : 0),
                    'total_pages' => $data['total_pages'] ?? 1,
                    'pages_processed' => $data['pages_processed'] ?? 1,
                ];
            }
            throw new \Exception($data['message'] ?? 'Gagal memberi nomor halaman pada dokumen.');
        }

        throw new \Exception("Terjadi kesalahan sistem saat memproses nomor halaman PDF: " . ($process->getErrorOutput() ?: $output));
    }
}
