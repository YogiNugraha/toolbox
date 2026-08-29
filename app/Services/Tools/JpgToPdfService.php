<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JpgToPdfService
{
    /**
     * Converts one or multiple uploaded images into a clean unified PDF document.
     *
     * @param array<UploadedFile> $images
     * @param string $layout 'a4_auto', 'a4_portrait', 'a4_landscape', 'fit'
     * @param string $margin 'none', 'small', 'normal'
     * @return array
     * @throws \Exception
     */
    public function convert(array $images, string $layout = 'a4_auto', string $margin = 'small'): array
    {
        if (empty($images)) {
            throw new \Exception("Setidaknya diperlukan 1 file gambar untuk dikonversi ke PDF.");
        }

        $pythonScript = base_path('storage/scripts/jpg2pdf.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script konversi JPG ke PDF tidak ditemukan di: {$pythonScript}");
        }

        // 1. Prepare target paths in local storage disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'converted_' . $uniqueId . '.pdf';
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        $absoluteResultPath = Storage::disk('local')->path($relativeResultPath);

        // 2. Prepare all input image file paths
        $tempStoredPaths = [];
        $realInputPaths = [];
        $totalOriginalSize = 0;

        foreach ($images as $img) {
            if (!$img instanceof UploadedFile) {
                continue;
            }
            $totalOriginalSize += $img->getSize();
            $realPath = $img->getRealPath();

            if (!$realPath || !file_exists($realPath)) {
                $tempRel = $img->store('temp_tools', 'local');
                $tempStoredPaths[] = $tempRel;
                $realInputPaths[] = Storage::disk('local')->path($tempRel);
            } else {
                $realInputPaths[] = $realPath;
            }
        }

        if (empty($realInputPaths)) {
            throw new \Exception("Gagal memproses file gambar input.");
        }

        // 3. Command execution: python jpg2pdf.py <output> <layout> <margin> <img1> [img2] ...
        $cmd = array_merge([
            $pythonExecutable,
            $pythonScript,
            $absoluteResultPath,
            $layout,
            $margin
        ], $realInputPaths);

        $process = new Process($cmd);
        $process->setTimeout(180);
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
            $errorMsg = $data['message'] ?? $errorOutput ?? $output ?? 'Gagal mengonversi gambar ke PDF.';
            throw new \Exception($errorMsg);
        }

        if (!Storage::disk('local')->exists($relativeResultPath) || Storage::disk('local')->size($relativeResultPath) === 0) {
            throw new \Exception("File dokumen PDF hasil konversi gagal dibuat atau kosong.");
        }

        $firstImgName = pathinfo($images[0]->getClientOriginalName(), PATHINFO_FILENAME);
        $downloadName = count($images) > 1 
            ? ($firstImgName . '_dan_' . (count($images) - 1) . '_foto_lainnya.pdf')
            : ($firstImgName . '.pdf');

        $resultSize = Storage::disk('local')->size($relativeResultPath);

        return [
            'path' => $relativeResultPath,
            'result_path' => $relativeResultPath,
            'filename' => $outputFilename,
            'result_filename' => $downloadName,
            'original_size' => $totalOriginalSize,
            'result_size' => $resultSize,
            'new_size' => $resultSize,
            'page_count' => $data['page_count'] ?? count($images),
            'images_count' => count($images),
            'layout' => $layout,
            'margin' => $margin,
            'extension' => 'pdf',
        ];
    }
}
