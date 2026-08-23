<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\PdfToExcelService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PdfToExcel extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;

    // Output results state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $pageCount;
    public $tableCount;
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

    public function convert(PdfToExcelService $converter, EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        $user = auth()->user();
        $toolSlug = 'pdf-to-excel';

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

            $result = $converter->convert($this->file);

            $this->resultPath = $result['path'];
            $this->resultFilename = $result['original_name'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['result_size'];
            $this->pageCount = $result['page_count'];
            $this->tableCount = $result['table_count'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'page_count' => $this->pageCount,
                    'table_count' => $this->tableCount,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Tabel data PDF berhasil diekstrak ke spreadsheet Excel!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal mengonversi PDF ke Excel: ' . $e->getMessage();
            $this->toast('Konversi gagal.', 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->pageCount = null;
        $this->tableCount = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('spreadsheet_' . time() . '.xlsx');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        // Fallback check on absolute path
        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('spreadsheet_' . time() . '.xlsx');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File hasil spreadsheet tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'pdf-to-excel';

        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.pdf-to-excel', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
