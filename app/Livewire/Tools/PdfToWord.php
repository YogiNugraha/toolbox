<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ConvertPdfToWordJob;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Validation\ValidationException;

class PdfToWord extends Component
{
    use WithFileUploads;

    public $file;
    public $jobId;
    public $status; // null, processing, completed, failed
    public $errorMsg;
    
    public $resultPath;
    public $originalSize;
    public $newSize;

    public function updatedFile()
    {
        $this->resetResult();
        $this->validateFile();
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        
        $user = auth()->user();
        $entitlementService = app(\App\Services\EntitlementService::class);
        $plan = $entitlementService->getCurrentPlan($user);
        $maxMb = config("plans.{$plan}.limits.pdf-to-word.max_file_size_mb") ?? 50; // default 50 if unlimited
        $maxKb = $maxMb * 1024;

        try {
            $this->validate([
                'file' => "required|file|mimes:pdf|max:{$maxKb}",
            ], [
                'file.mimes' => 'File harus berupa PDF.',
                'file.max' => "Ukuran maksimal {$maxMb}MB."
            ]);

            if ($this->file) {
                $realMime = $this->file->getMimeType();
                if ($realMime !== 'application/pdf') {
                    throw ValidationException::withMessages([
                        'file' => 'Format file tidak valid. Ekstensi file mungkin dipalsukan.'
                    ]);
                }
            }
        } catch (ValidationException $e) {
            LivewireAlert::title('Format file tidak didukung atau terlalu besar.')->error()->toast()->position('top-end')->timer(4000)->show();
            throw $e;
        }
    }

    public function convert(\App\Services\EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }
        
        $user = auth()->user();
        $toolSlug = 'pdf-to-word';

        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota harian Anda sudah habis. Silakan upgrade ke Pro.';
            LivewireAlert::title('Kuota harian kamu sudah habis.')->info()->toast()->position('top-end')->show();
            return;
        }

        if (!$entitlementService->canProcessFile($user, $toolSlug, $this->file->getSize())) {
            $this->errorMsg = 'Ukuran file melebihi batas paket Anda. Silakan upgrade ke Pro.';
            return;
        }

        try {
            $this->jobId = Str::uuid()->toString();
            $this->status = 'processing';
            
            // Store file temporarily
            $filename = $this->jobId . '.pdf';
            $userId = auth()->id() ?? 'guest';
            $relativePath = 'private/users/' . $userId . '/' . $filename;
            
            $inputPath = Storage::disk('local')->path($relativePath);
            $outputDir = Storage::disk('local')->path('private/users/' . $userId);
            
            // Move file to our persistent temp folder since getRealPath() will disappear after request
            $this->file->storeAs('private/users/' . $userId, $filename, 'local');
            
            $activity = \App\Models\Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => 'pdf-to-word',
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            // Dispatch the job
            ConvertPdfToWordJob::dispatch($this->jobId, $inputPath, $outputDir, $activity->id);

        } catch (\Exception $e) {
            $this->status = 'failed';
            $this->errorMsg = 'Gagal memulai konversi: ' . $e->getMessage();
        }
    }

    public function checkStatus()
    {
        if ($this->status !== 'processing' || !$this->jobId) {
            return;
        }

        $currentStatus = Cache::get("pdf_conversion_status_{$this->jobId}");
        
        if ($currentStatus === 'completed') {
            $this->status = 'completed';
            $result = Cache::get("pdf_conversion_result_{$this->jobId}");
            if ($result) {
                $userId = auth()->id() ?? 'guest';
                $this->resultPath = 'private/users/' . $userId . '/' . pathinfo($result['path'], PATHINFO_BASENAME);
                $this->originalSize = $result['original_size'];
                $this->newSize = $result['new_size'];
            }
            LivewireAlert::title('PDF berhasil dikonversi ke Word!')->success()->toast()->position('top-end')->timer(3000)->show();
        } elseif ($currentStatus === 'failed') {
            $this->status = 'failed';
            $this->errorMsg = Cache::get("pdf_conversion_error_{$this->jobId}");
        }
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = 'converted_' . time() . '.docx';
            
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('local');
            
            return $disk->download($this->resultPath, $filename);
        }
        
        $this->errorMsg = 'File hasil konversi tidak ditemukan.';
    }

    public function resetResult()
    {
        $this->jobId = null;
        $this->status = null;
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->errorMsg = null;
    }

    public function render(\App\Services\EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'pdf-to-word';
        
        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $dailyLimit = config("plans.{$currentPlan}.limits.{$toolSlug}.daily_quota");
        $maxMb = config("plans.{$currentPlan}.limits.{$toolSlug}.max_file_size_mb");

        return view('livewire.tools.pdf-to-word', [
            'remainingQuota' => $remainingQuota,
            'dailyLimit' => $dailyLimit,
            'currentPlan' => $currentPlan,
            'maxMb' => $maxMb,
        ]);
    }
}
