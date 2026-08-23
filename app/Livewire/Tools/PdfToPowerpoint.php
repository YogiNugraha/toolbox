<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\PdfToPowerpointService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PdfToPowerpoint extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;

    // Output results state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $slideCount;
    public $resultFilename;
    public $errorMsg;

    public function updatedFile()
    {
        $this->resetResult();
        if (! $this->file) {
            $this->resetValidation();
            $this->resetErrorBag();
            return;
        }
        $this->validateFile();
    }

    public function resetFile()
    {
        $this->file = null;
        $this->resetResult();
        $this->resetValidation();
        $this->resetErrorBag();
        $this->errorMsg = null;
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        try {
            $this->validate([
                'file' => 'required|file|mimes:pdf|max:51200', // 50MB max
            ], [
                'file.mimes' => 'Format file tidak didukung. Harap unggah file dokumen PDF (.pdf).',
                'file.max' => 'Ukuran file PDF maksimal 50MB.'
            ]);

            if ($this->file) {
                $ext = strtolower($this->file->getClientOriginalExtension());
                if ($ext !== 'pdf') {
                    throw ValidationException::withMessages([
                        'file' => 'Ekstensi file tidak valid. Harap gunakan format PDF (.pdf).'
                    ]);
                }
            }
        } catch (ValidationException $e) {
            $this->toast('Format file tidak didukung atau melebihi batas ukuran.', 'error');
            throw $e;
        }
    }

    public function convert(PdfToPowerpointService $converter, EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        $user = auth()->user();
        $toolSlug = 'pdf-to-powerpoint';

        // 1. Quota check
        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota harian Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota harian kamu sudah habis.', 'info');
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

            $result = $converter->convert($this->file);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->slideCount = $result['slide_count'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Dokumen PDF berhasil dikonversi ke PowerPoint!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal mengonversi PDF ke PowerPoint: ' . $e->getMessage();
            $this->toast('Konversi gagal.', 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->slideCount = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('presentation_' . time() . '.pptx');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        // Fallback check on absolute path
        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('presentation_' . time() . '.pptx');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File hasil presentasi tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'pdf-to-powerpoint';

        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.pdf-to-powerpoint', compact('remainingQuota', 'monthlyLimit', 'maxMb', 'isPro', 'currentPlan'));
    }
}
