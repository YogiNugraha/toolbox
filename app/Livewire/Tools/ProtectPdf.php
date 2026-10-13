<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\ProtectPdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProtectPdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $userPassword = '';
    public $passwordConfirmation = '';
    public $allowPrint = true;
    public $allowCopy = true;

    // Inspection metadata
    public $detectedPages = 0;
    public $isAlreadyEncrypted = false;

    // Result output state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $totalPages;
    public $resultFilename;
    public $errorMsg;

    public function updatedFile(ProtectPdfService $service)
    {
        $this->resetResult();
        if (!$this->file) {
            $this->resetValidation();
            $this->resetErrorBag();
            $this->detectedPages = 0;
            $this->isAlreadyEncrypted = false;
            return;
        }

        $this->validateFile();

        try {
            $info = $service->inspect($this->file);
            $this->detectedPages = $info['total_pages'] ?? 1;
            $this->isAlreadyEncrypted = !empty($info['is_encrypted']) && !empty($info['needs_pass']);

            if ($this->isAlreadyEncrypted) {
                $this->errorMsg = 'Dokumen PDF ini sudah terproteksi password. Anda dapat membukanya terlebih dahulu melalui tool Unlock PDF.';
                $this->toast('PDF ini sudah memiliki password.', 'warning');
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

    public function validateSecurityInputs()
    {
        $this->validate([
            'userPassword' => 'required|string|min:4|max:64',
            'passwordConfirmation' => 'required|same:userPassword',
        ], [
            'userPassword.required' => 'Password pembuka PDF wajib diisi.',
            'userPassword.min' => 'Password minimal terdiri dari 4 karakter.',
            'userPassword.max' => 'Password maksimal 64 karakter.',
            'passwordConfirmation.required' => 'Konfirmasi password wajib diisi.',
            'passwordConfirmation.same' => 'Konfirmasi password tidak cocok dengan password di atas.',
        ]);
    }

    public function protect(ProtectPdfService $service, EntitlementService $entitlementService)
    {
        $this->validateFile();
        $this->validateSecurityInputs();

        if (!$this->file) {
            return;
        }

        if ($this->isAlreadyEncrypted) {
            $this->toast('File ini sudah terproteksi password.', 'error');
            return;
        }

        $user = auth()->user();
        $toolSlug = 'protect-pdf';

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

            $result = $service->protect(
                $this->file,
                $this->userPassword,
                null,
                (bool)$this->allowPrint,
                (bool)$this->allowCopy
            );

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
                    'allow_print' => $this->allowPrint,
                    'allow_copy' => $this->allowCopy,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Dokumen PDF berhasil diproteksi dengan enkripsi AES-256!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal memproteksi PDF: ' . $e->getMessage();
            $this->toast('Proses proteksi gagal: ' . $e->getMessage(), 'error');
        }
    }

    public function resetFile()
    {
        $this->file = null;
        $this->userPassword = '';
        $this->passwordConfirmation = '';
        $this->allowPrint = true;
        $this->allowCopy = true;
        $this->detectedPages = 0;
        $this->isAlreadyEncrypted = false;
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
            $filename = $this->resultFilename ?: ('protected_document_' . time() . '.pdf');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('protected_document_' . time() . '.pdf');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File PDF hasil proteksi tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'protect-pdf';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.protect-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
