<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\CropImageService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CropImage extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;

    // Image original dimensions
    public $origWidth = 0;
    public $origHeight = 0;

    // Crop settings
    public $preset = '1:1'; // '1:1', '4:3', '16:9', '9:16', '3:2', 'pas_foto_2x3', 'pas_foto_3x4', 'pas_foto_4x6', 'custom'
    public $position = 'center'; // 'center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'
    public $customWidth = 400;
    public $customHeight = 400;
    public $customX = 0;
    public $customY = 0;
    public $quality = 90;
    public $format = 'original'; // 'original', 'jpg', 'png', 'webp'

    // Result output state
    public $resultPath;
    public $resultFilename;
    public $originalSize;
    public $newSize;
    public $finalWidth;
    public $finalHeight;
    public $errorMsg;

    public function updatedFile(CropImageService $service)
    {
        $this->resetResult();
        if (!$this->file) {
            $this->resetValidation();
            $this->resetErrorBag();
            $this->origWidth = 0;
            $this->origHeight = 0;
            return;
        }

        $this->validateFile();

        try {
            $info = $service->inspect($this->file);
            $this->origWidth = $info['width'];
            $this->origHeight = $info['height'];
            $this->customWidth = min($info['width'], 400);
            $this->customHeight = min($info['height'], 400);
        } catch (\Throwable $e) {
            $this->origWidth = 800;
            $this->origHeight = 600;
        }
    }

    public function setPreset($p)
    {
        $this->preset = $p;
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        try {
            $this->validate([
                'file' => 'required|image|mimes:jpeg,jpg,png,webp,bmp,gif|max:30720', // 30MB
            ], [
                'file.image' => 'File harus berupa gambar.',
                'file.mimes' => 'Format gambar yang didukung: JPG, PNG, WebP, BMP, GIF.',
                'file.max' => 'Ukuran gambar maksimal 30MB.',
            ]);
        } catch (ValidationException $e) {
            $this->toast('Format file tidak didukung atau melebihi batas 30MB.', 'error');
            throw $e;
        }
    }

    public function crop(CropImageService $service, EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        $user = auth()->user();
        $toolSlug = 'crop-image';

        // 1. Quota check
        $remaining = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota bulanan kamu sudah habis.', 'info');
            return;
        }

        try {
            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            $options = [
                'preset' => $this->preset,
                'position' => $this->position,
                'width' => (int) $this->customWidth,
                'height' => (int) $this->customHeight,
                'x' => (int) $this->customX,
                'y' => (int) $this->customY,
                'quality' => (int) $this->quality,
                'format' => $this->format,
            ];

            $result = $service->crop($this->file, $options);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->finalWidth = $result['final_width'];
            $this->finalHeight = $result['final_height'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'final_width' => $this->finalWidth,
                    'final_height' => $this->finalHeight,
                    'preset' => $this->preset,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Gambar berhasil dipotong sesuai rasio!', 'success');

        } catch (\Throwable $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $cleanMsg = explode("\n", trim($e->getMessage()))[0] ?? 'Terjadi kesalahan sistem.';
            $this->errorMsg = 'Gagal memotong gambar: ' . $cleanMsg;
            $this->toast('Proses gagal: ' . $cleanMsg, 'error');
        }
    }

    public function resetFile()
    {
        $this->file = null;
        $this->origWidth = 0;
        $this->origHeight = 0;
        $this->resetResult();
        $this->resetValidation();
        $this->resetErrorBag();
        $this->errorMsg = null;
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->finalWidth = null;
        $this->finalHeight = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('cropped_image_' . time() . '.jpg');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        $this->errorMsg = 'File hasil tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'crop-image';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 10;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 30;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.crop-image', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
