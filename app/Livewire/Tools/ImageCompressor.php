<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\ImageProcessorService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

class ImageCompressor extends Component
{
    use WithFileUploads;

    public $file;
    public $preset = 'sosmed'; // sosmed, website, custom

    // Website preset options
    public $websiteConvertToWebp = false;

    // Custom preset options
    public $customQuality = 80;
    public $customResize = false;
    public $customWidth = null;
    public $customHeight = null;
    public $customFormat = 'original'; // original, jpg, png, webp

    // Result data
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $resultExtension;
    
    public $errorMsg;

    public function updatedFile()
    {
        $this->resetResult();
        $this->validateFile();
    }

    public function updatedPreset()
    {
        $this->resetResult();
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        $this->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp|max:20480', // 20MB max
        ], [
            'file.mimes' => 'Format file tidak didukung. Gunakan JPG, PNG, atau WebP.',
            'file.max' => 'Ukuran file maksimal 20MB.'
        ]);

        if ($this->file) {
            $realMime = $this->file->getMimeType();
            if (!in_array($realMime, ['image/jpeg', 'image/png', 'image/webp'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file' => 'Format file tidak valid. Ekstensi file mungkin dipalsukan.'
                ]);
            }
        }
    }

    public function compress(ImageProcessorService $processor, \App\Services\EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        $user = auth()->user();
        $toolSlug = 'compress-image';

        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota harian Anda sudah habis. Silakan upgrade ke Pro.';
            return;
        }

        if ($this->preset === 'custom' && $entitlementService->isFeatureLocked($user, $toolSlug, 'preset_custom')) {
            $this->errorMsg = 'Fitur Custom hanya tersedia untuk pengguna Pro. Silakan upgrade.';
            return;
        }

        try {
            $quality = 80;
            $maxDimension = null;
            $outputFormat = null;

            if ($this->preset === 'sosmed') {
                $quality = 80;
                $maxDimension = 1080;
                // Output format same as input, but if PNG is large we can offer convert. Let's stick to simple: keep original unless requested.
            } elseif ($this->preset === 'website') {
                $quality = 75;
                $maxDimension = 1920;
                if ($this->websiteConvertToWebp) {
                    $outputFormat = 'webp';
                }
            } elseif ($this->preset === 'custom') {
                $quality = $this->customQuality;
                
                if ($this->customResize && ($this->customWidth || $this->customHeight)) {
                    // For simplicity in the service we only pass maxDimension, but to be robust we can just pass the larger of width/height
                    $maxDimension = max((int) $this->customWidth, (int) $this->customHeight);
                }

                if ($this->customFormat !== 'original') {
                    $outputFormat = $this->customFormat;
                }
            }

            $activity = \App\Models\Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => 'compress-image',
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            $result = $processor->process($this->file, $quality, $maxDimension, $outputFormat);

            $this->resultPath = $result['path'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->resultExtension = $result['extension'];

            $activity->update([
                'result_size' => $result['new_size'],
                'result_path' => $result['path'],
                'status' => 'completed',
                'meta' => [
                    'preset' => $this->preset,
                    'quality' => $quality,
                    'output_format' => $outputFormat ?? 'original'
                ],
            ]);

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update(['status' => 'failed', 'meta' => ['error' => $e->getMessage()]]);
            }
            $this->errorMsg = 'Gagal mengompres gambar: ' . $e->getMessage();
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->errorMsg = null;
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = 'compressed_' . time() . '.' . $this->resultExtension;
            return response()->download(Storage::disk('local')->path($this->resultPath), $filename);
        }
        
        $this->errorMsg = 'File tidak ditemukan atau sudah kadaluarsa.';
    }

    public function render(\App\Services\EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'compress-image';
        
        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $isCustomLocked = $entitlementService->isFeatureLocked($user, $toolSlug, 'preset_custom');
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $dailyLimit = config("plans.{$currentPlan}.limits.{$toolSlug}.daily_quota");

        return view('livewire.tools.image-compressor', [
            'remainingQuota' => $remainingQuota,
            'isCustomLocked' => $isCustomLocked,
            'dailyLimit' => $dailyLimit,
            'currentPlan' => $currentPlan,
        ]);
    }
}
