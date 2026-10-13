<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\UnlockPdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UnlockPdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $password = '';

    // Inspection state
    public $detectedPages = 0;
    public $isEncrypted = false;
    public $needsPass = false;

    // Result output state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $totalPages;
    public $resultFilename;
    public $errorMsg;

    public function updatedFile(UnlockPdfService $service)
    {
        $this->resetResult();
        if (!$this->file) {
            $this->resetValidation();
            $this->resetErrorBag();
            $this->detectedPages = 0;
            $this->isEncrypted = false;
            $this->needsPass = false;
            $this->password = '';
            return;
        }

        $this->validateFile();

        try {
            $check = $service->check($this->file);
            $this->isEncrypted = !empty($check['is_encrypted']);
            $this->needsPass = !empty($check['needs_pass']);
            $this->detectedPages = $check['total_pages'] ?? 1;

            if (!$this->isEncrypted && !$this->needsPass) {
                $this->errorMsg = 'Dokumen PDF ini tidak memiliki proteksi password (sudah bebas dibuka). Anda tetap dapat memprosesnya jika ingin membuat salinan bersih.';
            }
        } catch (\Throwable $e) {
            $this->detectedPages = 1;
        }
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        try {
            $this->validate([
                'file' => 'required|file|mimes:pdf|max:51200', // 50MB
            ], [
                'file.mimes' => 'Format file tidak didukung. Harap unggah file dokumen PDF (.pdf).',
                'file.max' => 'Ukuran file PDF maksimal 50MB.',
            ]);
        } catch (ValidationException $e) {
            $this->toast('Format file tidak didukung atau melebihi batas ukuran.', 'error');
            throw $e;
        }
    }

    public function unlock(UnlockPdfService $service, EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        if ($this->needsPass && empty($this->password)) {
            $this->addError('password', 'Password wajib diisi untuk membuka dokumen yang terenkripsi.');
            $this->toast('Silakan masukkan password dokumen PDF.', 'error');
            return;
        }

        $user = auth()->user();
        $toolSlug = 'unlock-pdf';

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

            $result = $service->unlock($this->file, (string)$this->password);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->totalPages = $result['total_pages'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'total_pages' => $this->totalPages,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Proteksi dokumen PDF berhasil dihapus secara permanen!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal membuka proteksi PDF: ' . $e->getMessage();
            $this->toast($e->getMessage(), 'error');
        }
    }

    public function resetFile()
    {
        $this->file = null;
        $this->password = '';
        $this->detectedPages = 0;
        $this->isEncrypted = false;
        $this->needsPass = false;
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
        $this->totalPages = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('unlocked_document_' . time() . '.pdf');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('unlocked_document_' . time() . '.pdf');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File PDF hasil unlock tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'unlock-pdf';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.unlock-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
