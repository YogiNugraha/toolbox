<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\SplitPdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SplitPdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $mode = 'all'; // 'all' (zip) or 'range' (pdf)
    public $pageRange = '';
    public $detectedPages = null;

    // Output results state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $originalPages;
    public $extractedPages;
    public $extension;
    public $resultFilename;
    public $errorMsg;

    public function updatedFile(SplitPdfService $splitter)
    {
        $this->resetResult();
        if (! $this->file) {
            $this->resetValidation();
            $this->resetErrorBag();
            $this->detectedPages = null;
            return;
        }
        $this->validateFile();

        try {
            $this->detectedPages = $splitter->getPageCount($this->file);
            if (empty($this->pageRange) && $this->detectedPages > 1) {
                $this->pageRange = "1-" . min(3, $this->detectedPages);
            }
        } catch (\Throwable $e) {
            $this->detectedPages = null;
        }
    }

    public function setMode($newMode)
    {
        $this->mode = $newMode;
        $this->resetResult();
    }

    public function resetFile()
    {
        $this->file = null;
        $this->detectedPages = null;
        $this->pageRange = '';
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

    public function split(SplitPdfService $splitter, EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        if ($this->mode === 'range' && empty(trim($this->pageRange))) {
            $this->errorMsg = 'Harap tentukan rentang halaman yang ingin diekstrak (contoh: 1-3, 5).';
            $this->toast('Rentang halaman belum diisi.', 'error');
            return;
        }

        $user = auth()->user();
        $toolSlug = 'split-pdf';

        // 1. Quota check
        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
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

            $result = $splitter->split($this->file, $this->mode, $this->pageRange);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->originalPages = $result['original_pages'];
            $this->extractedPages = $result['extracted_pages'];
            $this->extension = $result['extension'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'mode' => $this->mode,
                    'original_pages' => $this->originalPages,
                    'extracted_pages' => $this->extractedPages,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Dokumen PDF berhasil dipisahkan!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal memisahkan PDF: ' . $e->getMessage();
            $this->toast('Pemisahan gagal.', 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->originalPages = null;
        $this->extractedPages = null;
        $this->extension = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('split_output_' . time() . '.' . ($this->extension ?: 'pdf'));
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('split_output_' . time() . '.' . ($this->extension ?: 'pdf'));
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File hasil pemisahan tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'split-pdf';

        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.split-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
