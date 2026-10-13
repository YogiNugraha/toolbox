<?php

namespace App\Services\Tools;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResizeImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Inspect image dimensions and metadata.
     */
    public function inspect(UploadedFile $file): array
    {
        try {
            $image = $this->manager->decodePath($file->getRealPath());
            return [
                'width' => $image->width(),
                'height' => $image->height(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'extension' => strtolower($file->getClientOriginalExtension()),
            ];
        } catch (\Throwable $e) {
            return [
                'width' => 800,
                'height' => 600,
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'extension' => 'jpg',
            ];
        }
    }

    /**
     * Resize image according to dimensions or percentage scale.
     */
    public function resize(UploadedFile $file, array $options): array
    {
        $originalSize = $file->getSize();
        $image = $this->manager->decodePath($file->getRealPath());

        $origW = $image->width();
        $origH = $image->height();

        $mode = $options['mode'] ?? 'dimensions'; // 'dimensions' or 'percentage'
        $maintainAspectRatio = !empty($options['maintain_aspect_ratio']);
        $quality = (int) ($options['quality'] ?? 90);
        $outputFormat = strtolower($options['format'] ?? 'original');

        if ($mode === 'percentage') {
            $percent = max(1, min(500, (int) ($options['percentage'] ?? 50)));
            $targetW = max(1, (int) round($origW * ($percent / 100)));
            $targetH = max(1, (int) round($origH * ($percent / 100)));
            $image->resize($targetW, $targetH);
        } else {
            $targetW = !empty($options['width']) ? (int) $options['width'] : null;
            $targetH = !empty($options['height']) ? (int) $options['height'] : null;

            if ($targetW && $targetH) {
                if ($maintainAspectRatio) {
                    $image->scale(width: $targetW, height: $targetH);
                } else {
                    $image->resize($targetW, $targetH);
                }
            } elseif ($targetW) {
                $image->scale(width: $targetW);
            } elseif ($targetH) {
                $image->scale(height: $targetH);
            }
        }

        // Determine output extension
        $origExt = strtolower($file->getClientOriginalExtension());
        if ($origExt === 'jpeg') $origExt = 'jpg';

        $ext = ($outputFormat === 'original' || empty($outputFormat)) ? $origExt : $outputFormat;
        if ($ext === 'jpeg') $ext = 'jpg';

        // Encode image
        if ($ext === 'png') {
            $encoded = $image->encodeUsingFileExtension('png');
        } elseif ($ext === 'webp') {
            $encoded = $image->encodeUsingFileExtension('webp', quality: $quality);
        } else {
            $encoded = $image->encodeUsingFileExtension('jpg', quality: $quality);
        }

        // Store to local private disk
        $uniqueId = Str::uuid()->toString();
        $userId = auth()->id() ?? 'guest';
        $relativeDir = 'users/' . $userId;
        $outputFilename = 'resized_' . $uniqueId . '.' . $ext;
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        Storage::disk('local')->put($relativeResultPath, (string) $encoded);

        $newSize = Storage::disk('local')->size($relativeResultPath);
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return [
            'result_path' => $relativeResultPath,
            'result_filename' => $baseName . '_resized.' . $ext,
            'original_size' => $originalSize,
            'new_size' => $newSize,
            'final_width' => $image->width(),
            'final_height' => $image->height(),
            'orig_width' => $origW,
            'orig_height' => $origH,
            'extension' => $ext,
        ];
    }
}
