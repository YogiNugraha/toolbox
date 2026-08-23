<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\MergePdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MergePdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $files = [];

    // Output results state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $filesCount;
    public $pageCount;
    public $resultFilename;
    public $errorMsg;

    public function updatedFiles()
    {
        $this->resetResult();
        $this->validateFiles();
    }

    public function validateFiles()
    {
        $this->errorMsg = null;
        try {
            $this->validate([
                'files' => 'required|array|min:2',
                'files.*' => 'required|file|mimes:pdf|max:51200', // 50MB each
            ], [
                'files.min' => 'Pilih minimal 2 file dokumen PDF untuk digabungkan.',
                'files.*.mimes' => 'Format file harus berupa dokumen PDF (.pdf).',
                'files.*.max' => 'Ukuran setiap file PDF maksimal 50MB.'
            ]);
        } catch (ValidationException $e) {
            $this->toast('Format file tidak valid atau kurang dari 2 file PDF.', 'error');
            throw $e;
        }
    }

    public function moveUp($index)
    {
        if ($index > 0 && isset($this->files[$index]) && isset($this->files[$index - 1])) {
            $temp = $this->files[$index - 1];
            $this->files[$index - 1] = $this->files[$index];
            $this->files[$index] = $temp;
            $this->resetResult();
        }
    }

    public function moveDown($index)
    {
        if ($index < count($this->files) - 1 && isset($this->files[$index]) && isset($this->files[$index + 1])) {
            $temp = $this->files[$index + 1];
            $this->files[$index + 1] = $this->files[$index];
            $this->files[$index] = $temp;
            $this->resetResult();
        }
    }

    public function removeFile($index)
    {
        if (isset($this->files[$index])) {
            array_splice($this->files, $index, 1);
            $this->resetResult();
        }
    }

    public function resetFiles()
    {
        $this->files = [];
        $this->resetResult();
        $this->resetValidation();
        $this->resetErrorBag();
        $this->errorMsg = null;
    }

    public function merge(MergePdfService $merger, EntitlementService $entitlementService)
    {
        $this->validateFiles();

        if (count($this->files) < 2) {
            $this->errorMsg = 'Pilih minimal 2 file PDF untuk digabungkan.';
            return;
        }

        $user = auth()->user();
        $toolSlug = 'merge-pdf';

        // 1. Quota check
        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota bulanan kamu sudah habis.', 'info');
            return;
        }

        try {
            $totalSize = array_sum(array_map(fn($f) => $f->getSize(), $this->files));

            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => count($this->files) . ' File PDF (' . $this->files[0]->getClientOriginalName() . ' dkk)',
                'original_size' => $totalSize,
                'status' => 'processing',
            ]);

            $result = $merger->merge($this->files);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->filesCount = $result['files_count'];
            $this->pageCount = $result['page_count'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'files_count' => $this->filesCount,
                    'page_count' => $this->pageCount,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Dokumen PDF berhasil digabungkan menjadi satu file!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal menggabungkan PDF: ' . $e->getMessage();
            $this->toast('Penggabungan gagal.', 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->filesCount = null;
        $this->pageCount = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('merged_document_' . time() . '.pdf');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('merged_document_' . time() . '.pdf');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File hasil PDF gabungan tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'merge-pdf';

        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.merge-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
