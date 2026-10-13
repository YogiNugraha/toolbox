<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\WatermarkPdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WatermarkPdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $watermarkImage;

    // Watermark Configuration
    public $type = 'text'; // 'text' or 'image'
    public $text = 'CONFIDENTIAL';
    public $fontsize = 48;
    public $colorPreset = 'red'; // 'slate', 'red', 'blue', 'black', 'orange'
    public $opacity = 0.25;
    public $rotation = 45; // 45, 0, -45
    public $position = 'center'; // 'center', 'top', 'bottom', 'tiled'

    // Image Watermark Specific
    public $imageScale = 0.35;
    public $imagePosition = 'center';

    // Page Scope & Layering
    public $pageMode = 'all'; // 'all', 'first', 'range'
    public $pageRange = '';
    public $layer = 'over'; // 'over', 'under'

    // Inspection Metadata
    public $detectedPages = 0;
    public $isEncrypted = false;

    // Output Result State
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $totalPages;
    public $watermarkedPages;
    public $resultFilename;
    public $errorMsg;

    public function updatedFile(WatermarkPdfService $service)
    {
        $this->resetResult();
        if (!$this->file) {
            $this->resetValidation();
            $this->resetErrorBag();
            $this->detectedPages = 0;
            $this->isEncrypted = false;
            return;
        }

        $this->validateFile();

        try {
            $info = $service->inspect($this->file);
            $this->detectedPages = $info['total_pages'] ?? 1;
            $this->isEncrypted = !empty($info['is_encrypted']) && !empty($info['needs_pass']);

            if ($this->isEncrypted) {
                $this->errorMsg = 'Dokumen PDF ini terkunci password. Silakan buka proteksinya terlebih dahulu melalui tool Unlock PDF.';
                $this->toast('PDF terproteksi password.', 'error');
            }
        } catch (\Throwable $e) {
            $this->detectedPages = 1;
        }
    }

    public function setPreset(string $preset)
    {
        $this->text = $preset;
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

    public function validateWatermarkParams()
    {
        if ($this->type === 'text') {
            $this->validate([
                'text' => 'required|string|min:1|max:80',
            ], [
                'text.required' => 'Teks watermark tidak boleh kosong.',
                'text.max' => 'Teks watermark maksimal 80 karakter.',
            ]);
        } else {
            $this->validate([
                'watermarkImage' => 'required|image|mimes:png,jpg,jpeg,webp|max:10240', // 10MB
            ], [
                'watermarkImage.required' => 'Pilih berkas gambar/logo untuk dijadikan watermark.',
                'watermarkImage.image' => 'Berkas watermark harus berupa gambar (PNG, JPG, WebP).',
            ]);
        }

        if ($this->pageMode === 'range') {
            $this->validate([
                'pageRange' => 'required|string|regex:/^[0-9\s,\-]+$/',
            ], [
                'pageRange.required' => 'Rentang halaman wajib diisi.',
                'pageRange.regex' => 'Format rentang halaman tidak valid (contoh: 1-3, 5).',
            ]);
        }
    }

    public function applyWatermark(WatermarkPdfService $service, EntitlementService $entitlementService)
    {
        $this->validateFile();
        $this->validateWatermarkParams();

        if (!$this->file) {
            return;
        }

        if ($this->isEncrypted) {
            $this->toast('File PDF ini terproteksi password.', 'error');
            return;
        }

        $user = auth()->user();
        $toolSlug = 'watermark-pdf';

        // 1. Quota check
        $remaining = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota bulanan kamu sudah habis.', 'info');
            return;
        }

        // Color mapping
        $colorMap = [
            'slate' => [0.45, 0.50, 0.60],
            'red' => [0.85, 0.20, 0.20],
            'blue' => [0.20, 0.45, 0.90],
            'black' => [0.05, 0.05, 0.05],
            'orange' => [0.95, 0.45, 0.10],
        ];

        $config = [
            'type' => $this->type,
            'page_mode' => $this->pageMode,
            'page_range' => $this->pageRange,
            'layer' => $this->layer,
        ];

        if ($this->type === 'text') {
            $config['text'] = $this->text;
            $config['fontsize'] = (float)$this->fontsize;
            $config['color'] = $colorMap[$this->colorPreset] ?? [0.5, 0.5, 0.5];
            $config['opacity'] = (float)$this->opacity;
            $config['rotation'] = (int)$this->rotation;
            $config['position'] = $this->position;
        } else {
            $config['scale'] = (float)$this->imageScale;
            $config['opacity'] = (float)$this->opacity;
            $config['position'] = $this->imagePosition;
        }

        try {
            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            $result = $service->watermark($this->file, $config, $this->watermarkImage);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->totalPages = $result['total_pages'];
            $this->watermarkedPages = $result['watermarked_pages'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'total_pages' => $this->totalPages,
                    'watermarked_pages' => $this->watermarkedPages,
                    'type' => $this->type,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Watermark berhasil diterapkan pada dokumen PDF!', 'success');

        } catch (\Throwable $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $cleanMsg = explode("\n", trim($e->getMessage()))[0] ?? 'Terjadi kesalahan sistem.';
            $this->errorMsg = 'Gagal menambahkan watermark: ' . $cleanMsg;
            $this->toast('Proses watermark gagal: ' . $cleanMsg, 'error');
        }
    }

    public function resetFile()
    {
        $this->file = null;
        $this->watermarkImage = null;
        $this->detectedPages = 0;
        $this->isEncrypted = false;
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
        $this->watermarkedPages = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('watermarked_document_' . time() . '.pdf');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('watermarked_document_' . time() . '.pdf');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File PDF hasil watermark tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'watermark-pdf';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.watermark-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
