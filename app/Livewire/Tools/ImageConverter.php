<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\ImageProcessorService;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Validation\ValidationException;

class ImageConverter extends Component
{
    use WithFileUploads;

    public $file;
    public $outputFormat = 'png';
    public $errorMsg;
    
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $resultExtension;

    public function updatedFile()
    {
        $this->resetResult();
        $this->validateFile();
        
        // Auto-select a different output format than the input
        if ($this->file) {
            $ext = strtolower($this->file->getClientOriginalExtension());
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $this->outputFormat = 'png';
            } elseif ($ext === 'png') {
                $this->outputFormat = 'jpg';
            } else {
                $this->outputFormat = 'jpg';
            }
        }
    }

    public function updatedOutputFormat()
    {
        $this->resetResult();
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        try {
            $this->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,bmp|max:10240', // 10MB max
            ], [
                'file.mimes' => 'Format file tidak didukung.',
                'file.max' => 'Ukuran file maksimal 10MB.'
            ]);

            if ($this->file) {
                $realMime = $this->file->getMimeType();
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'];
                if (!in_array($realMime, $allowedMimes)) {
                    throw ValidationException::withMessages([
                        'file' => 'Format file tidak valid. Ekstensi file mungkin dipalsukan.'
                    ]);
                }
            }
        } catch (ValidationException $e) {
            LivewireAlert::title('Format file tidak didukung.')->error()->toast()->position('top-end')->timer(4000)->show();
            throw $e;
        }
    }

    public function convert(ImageProcessorService $processor, \App\Services\EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        $user = auth()->user();
        $toolSlug = 'convert-image';

        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota harian Anda sudah habis. Silakan upgrade ke Pro.';
            LivewireAlert::title('Kuota harian kamu sudah habis.')->info()->toast()->position('top-end')->show();
            return;
        }

        try {
            $activity = \App\Models\Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => 'convert-image',
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            // Quality 90 for conversion to preserve details
            $result = $processor->process($this->file, 90, null, $this->outputFormat);

            $this->resultPath = $result['path'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->resultExtension = $result['extension'];

            $activity->update([
                'result_size' => $result['new_size'],
                'result_path' => $result['path'],
                'status' => 'completed',
                'meta' => [
                    'output_format' => $this->outputFormat
                ],
            ]);

            LivewireAlert::title('Gambar berhasil dikonversi!')->success()->toast()->position('top-end')->timer(3000)->show();

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update(['status' => 'failed', 'meta' => ['error' => $e->getMessage()]]);
            }
            $this->errorMsg = 'Gagal mengonversi gambar: ' . $e->getMessage();
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
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if ($this->resultPath && $disk->exists($this->resultPath)) {
            $filename = 'converted_' . time() . '.' . $this->resultExtension;
            return $disk->download($this->resultPath, $filename);
        }
        
        $this->errorMsg = 'File tidak ditemukan atau sudah kadaluarsa.';
    }

    public function render(\App\Services\EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'convert-image';
        
        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $dailyLimit = config("plans.{$currentPlan}.limits.{$toolSlug}.daily_quota");

        return view('livewire.tools.image-converter', [
            'remainingQuota' => $remainingQuota,
            'dailyLimit' => $dailyLimit,
            'currentPlan' => $currentPlan,
        ]);
    }
}
