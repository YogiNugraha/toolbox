<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\PdfCompressorService;
use App\Services\EntitlementService;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Traits\LivewireLineoneAlerts;

class PdfCompressor extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $preset = 'recommended'; // extreme, recommended, low, custom

    // Custom preset options (Pro only)
    public $customDpi = 150;
    public $customQuality = 75;
    public $customStripMetadata = true;

    // Result properties
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $savingsPercentage;
    public $pageCount;
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

    public function updatedPreset()
    {
        $this->resetResult();
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        try {
            $this->validate([
                'file' => 'required|file|mimes:pdf|max:51200', // 50MB max
            ], [
                'file.mimes' => 'Format file tidak didukung. Harap unggah dokumen PDF (.pdf).',
                'file.max' => 'Ukuran file PDF maksimal 50MB.'
            ]);

            if ($this->file) {
                $realMime = $this->file->getMimeType();
                if (!in_array($realMime, ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf', 'text/x-pdf'])) {
                    throw ValidationException::withMessages([
                        'file' => 'Format file tidak valid atau bukan dokumen PDF asli.'
                    ]);
                }
            }
        } catch (ValidationException $e) {
            $this->toast('Format file tidak didukung atau melebihi batas ukuran.', 'error');
            throw $e;
        }
    }

    public function compress(PdfCompressorService $compressor, EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        $user = auth()->user();
        $toolSlug = 'compress-pdf';

        // 1. Quota check
        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota harian Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota harian kamu sudah habis.', 'info');
            return;
        }

        // 2. Pro feature lock check
        if ($this->preset === 'custom' && $entitlementService->isFeatureLocked($user, $toolSlug, 'preset_custom')) {
            $this->errorMsg = 'Pengaturan Kompresi Kustom hanya dapat diakses oleh pengguna paket Pro.';
            $this->toast('Fitur ini khusus pengguna Pro.', 'warning');
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

            $customOptions = [
                'dpi' => $this->customDpi,
                'quality' => $this->customQuality,
                'strip_metadata' => $this->customStripMetadata,
            ];

            $result = $compressor->compress($this->file, $this->preset, $customOptions);

            $this->resultPath = $result['path'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->savingsPercentage = $result['savings_percentage'];
            $this->pageCount = $result['page_count'];
            $this->resultFilename = $result['filename'];

            $activity->update([
                'result_size' => $result['new_size'],
                'result_path' => $result['path'],
                'status' => 'completed',
                'meta' => [
                    'preset' => $this->preset,
                    'savings_percentage' => $result['savings_percentage'],
                    'page_count' => $result['page_count'],
                ],
            ]);

            $this->toast('Dokumen PDF berhasil dikompres!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update(['status' => 'failed', 'meta' => ['error' => $e->getMessage()]]);
            }
            $this->errorMsg = 'Gagal mengompres PDF: ' . $e->getMessage();
            $this->toast('Terjadi kendala saat memproses PDF.', 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->savingsPercentage = null;
        $this->pageCount = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('compressed_' . time() . '.pdf');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        // Fallback check on absolute path
        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('compressed_' . time() . '.pdf');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File hasil kompresi tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'compress-pdf';

        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $isCustomLocked = $entitlementService->isFeatureLocked($user, $toolSlug, 'preset_custom');
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $dailyLimit = $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.pdf-compressor', compact('remainingQuota', 'dailyLimit', 'isCustomLocked', 'isPro', 'currentPlan'));
    }
}
