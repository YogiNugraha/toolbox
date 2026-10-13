<?php

namespace App\Services\Tools;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CropImageService
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
     * Crop image based on ratio preset or custom coordinates.
     */
    public function crop(UploadedFile $file, array $options): array
    {
        $originalSize = $file->getSize();
        $image = $this->manager->decodePath($file->getRealPath());

        $origW = $image->width();
        $origH = $image->height();

        $preset = $options['preset'] ?? '1:1';
        $position = $options['position'] ?? 'center'; // 'center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'
        $quality = (int) ($options['quality'] ?? 90);
        $outputFormat = strtolower($options['format'] ?? 'original');

        $cropW = $origW;
        $cropH = $origH;
        $offsetX = 0;
        $offsetY = 0;

        if ($preset === 'custom') {
            $cropW = min($origW, max(10, (int) ($options['width'] ?? $origW)));
            $cropH = min($origH, max(10, (int) ($options['height'] ?? $origH)));
            $offsetX = max(0, min($origW - $cropW, (int) ($options['x'] ?? 0)));
            $offsetY = max(0, min($origH - $cropH, (int) ($options['y'] ?? 0)));
        } else {
            // Calculate ratio
            $ratios = [
                '1:1' => 1.0,
                '4:3' => 4.0 / 3.0,
                '16:9' => 16.0 / 9.0,
                '9:16' => 9.0 / 16.0,
                '3:2' => 3.0 / 2.0,
                '2:3' => 2.0 / 3.0,
                'pas_foto_2x3' => 2.0 / 3.0,
                'pas_foto_3x4' => 3.0 / 4.0,
                'pas_foto_4x6' => 4.0 / 6.0,
            ];

            $targetRatio = $ratios[$preset] ?? 1.0;
            $currentRatio = $origW / max(1, $origH);

            if ($currentRatio > $targetRatio) {
                // Image is wider than target ratio
                $cropH = $origH;
                $cropW = (int) round($origH * $targetRatio);
            } else {
                // Image is taller than target ratio
                $cropW = $origW;
                $cropH = (int) round($origW / $targetRatio);
            }

            // Calculate offsets based on position
            if ($position === 'center') {
                $offsetX = (int) round(($origW - $cropW) / 2);
                $offsetY = (int) round(($origH - $cropH) / 2);
            } elseif ($position === 'top-left') {
                $offsetX = 0;
                $offsetY = 0;
            } elseif ($position === 'top-right') {
                $offsetX = $origW - $cropW;
                $offsetY = 0;
            } elseif ($position === 'bottom-left') {
                $offsetX = 0;
                $offsetY = $origH - $cropH;
            } elseif ($position === 'bottom-right') {
                $offsetX = $origW - $cropW;
                $offsetY = $origH - $cropH;
            }
        }

        // Apply crop
        $image->crop(
            width: max(1, $cropW),
            height: max(1, $cropH),
            x: max(0, $offsetX),
            y: max(0, $offsetY)
        );

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
        $outputFilename = 'cropped_' . $uniqueId . '.' . $ext;
        $relativeResultPath = $relativeDir . '/' . $outputFilename;

        Storage::disk('local')->makeDirectory($relativeDir);
        Storage::disk('local')->put($relativeResultPath, (string) $encoded);

        $newSize = Storage::disk('local')->size($relativeResultPath);
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return [
            'result_path' => $relativeResultPath,
            'result_filename' => $baseName . '_cropped.' . $ext,
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
