<?php

namespace App\Services\Tools;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageProcessorService
{
    /**
     * @var ImageManager
     */
    protected $manager;

    public function __construct()
    {
        // Default to GD driver. If Imagick is required, change to Imagick Driver.
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Process an uploaded image: resize, compress, and optionally convert format.
     *
     * @param UploadedFile $file
     * @param int $quality
     * @param int|null $maxDimension
     * @param string|null $outputFormat (e.g. 'jpg', 'png', 'webp')
     * @return array
     */
    public function process(UploadedFile $file, int $quality = 80, ?int $maxDimension = null, ?string $outputFormat = null): array
    {
        $originalSize = $file->getSize();
        
        // Read image from file (v4 syntax)
        $image = $this->manager->decodePath($file->getRealPath());

        // In Intervention Image v4, EXIF data is stripped by default upon encoding unless specified.
        
        // Scale down if a max dimension is provided and the image is larger than that
        if ($maxDimension) {
            $width = $image->width();
            $height = $image->height();

            if ($width > $maxDimension || $height > $maxDimension) {
                // scaleDown maintains aspect ratio and only scales if larger
                $image->scaleDown(width: $maxDimension, height: $maxDimension);
            }
        }

        // Determine format and extension
        $extension = $outputFormat ? strtolower($outputFormat) : strtolower($file->getClientOriginalExtension());
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        // Generate a unique filename and determine storage path
        $filename = Str::uuid() . '.' . $extension;
        $userId = auth()->id() ?? 'guest';
        $relativePath = 'private/users/' . $userId . '/' . $filename;
        
        // Encode the image with the specific format and quality (v4 syntax)
        if ($extension === 'png') {
            // PNG quality is usually handled differently, we can just pass default options
            $encoded = $image->encodeUsingFileExtension($extension);
        } else {
            $encoded = $image->encodeUsingFileExtension($extension, quality: $quality);
        }

        // Save the encoded image to the private storage
        Storage::disk('local')->put($relativePath, (string) $encoded);

        $newSize = Storage::disk('local')->size($relativePath);

        return [
            'path' => $relativePath,
            'original_size' => $originalSize,
            'new_size' => $newSize,
            'extension' => $extension
        ];
    }
}
