<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\PageNumberPdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;

class PageNumberPdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $position = 'bottom-center'; // 'bottom-center', 'bottom-right', 'bottom-left', 'top-center', 'top-right', 'top-left'
    public $format = 'page_n_of_total'; // 'page_n_of_total', 'page_n', 'n', 'n_slash_total', 'roman_lower', 'roman_upper'
    public $startPage = 1;
    public $firstNumber = 1;
    public $fontSize = 10;
    public $fontColor = '#334155';
    public $previewPage = 1;

    // File inspection & preview state
    public $totalPages = 0;
    public $isEncrypted = false;
    public $previewUri;

    // Result state
    public $resultPath;
    public $resultFilename;
    public $originalSize;
    public $newSize;
    public $pagesProcessed = 0;
    public $errorMsg;

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|mimes:pdf|max:51200',
        ], [
            'file.required' => 'Silakan pilih dokumen PDF.',
            'file.mimes' => 'Format berkas harus berupa dokumen PDF (.pdf).',
            'file.max' => 'Ukuran dokumen maksimal adalah 50MB.',
        ]);

        $this->resetResult();
        $this->resetValidation();

        try {
            $service = app(PageNumberPdfService::class);
            $inspect = $service->inspect($this->file);

            if (!empty($inspect['is_encrypted']) && !empty($inspect['needs_pass'])) {
                $this->errorMsg = 'Dokumen PDF ini diproteksi password. Silakan buka proteksinya terlebih dahulu di tool Buka Proteksi PDF.';
                $this->isEncrypted = true;
                $this->toast('PDF terproteksi kata sandi.', 'warning');
                return;
            }

            $this->totalPages = $inspect['total_pages'] ?? 1;
            $this->previewPage = 1;
            $this->updatePreview($service);

        } catch (\Throwable $e) {
            $this->errorMsg = 'Gagal memeriksa dokumen: ' . $e->getMessage();
            $this->toast('Gagal memvalidasi PDF.', 'error');
        }
    }

    public function updated($property, $value)
    {
        if (in_array($property, ['position', 'format', 'startPage', 'firstNumber', 'fontSize', 'fontColor', 'previewPage'])) {
            if ($this->file && !$this->isEncrypted) {
                $service = app(PageNumberPdfService::class);
                $this->updatePreview($service);
            }
        }
    }

    public function updatePreview(PageNumberPdfService $service)
    {
        if (!$this->file) {
            return;
        }

        $options = [
            'position' => $this->position,
            'format' => $this->format,
            'font_size' => (int) $this->fontSize,
            'font_color' => $this->fontColor,
            'start_page' => max(1, (int) $this->startPage),
            'first_number' => max(1, (int) $this->firstNumber),
            'margin_x' => 36,
            'margin_y' => 36,
        ];

        try {
            $this->previewUri = $service->renderPreview($this->file, $options, (int) $this->previewPage);
        } catch (\Throwable $e) {
            // Keep existing preview if preview render temporarily fails
        }
    }

    public function setPreviewPage(int $page)
    {
        $this->previewPage = max(1, min($page, $this->totalPages));
        $service = app(PageNumberPdfService::class);
        $this->updatePreview($service);
    }

    public function process(PageNumberPdfService $service, EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'page-number-pdf';

        if (!$this->file) {
            $this->errorMsg = 'Silakan pilih dokumen PDF terlebih dahulu.';
            $this->toast('Dokumen PDF belum dipilih.', 'warning');
            return;
        }

        // Quota check
        $remaining = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota bulanan kamu sudah habis.', 'info');
            return;
        }

        $options = [
            'position' => $this->position,
            'format' => $this->format,
            'font_size' => (int) $this->fontSize,
            'font_color' => $this->fontColor,
            'start_page' => max(1, (int) $this->startPage),
            'first_number' => max(1, (int) $this->firstNumber),
            'margin_x' => 36,
            'margin_y' => 36,
        ];

        try {
            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            $result = $service->paginate($this->file, $options);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->pagesProcessed = $result['pages_processed'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'position' => $this->position,
                    'format' => $this->format,
                    'start_page' => $this->startPage,
                    'pages_processed' => $this->pagesProcessed,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Nomor halaman berhasil ditambahkan!', 'success');

        } catch (\Throwable $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $cleanMsg = explode("\n", trim($e->getMessage()))[0] ?? 'Terjadi kesalahan sistem.';
            $this->errorMsg = 'Gagal memproses dokumen: ' . $cleanMsg;
            $this->toast('Proses gagal: ' . $cleanMsg, 'error');
        }
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: 'dokumen_penomoran.pdf';
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        $this->errorMsg = 'File hasil tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function resetFile()
    {
        $this->file = null;
        $this->resetResult();
        $this->totalPages = 0;
        $this->previewPage = 1;
        $this->previewUri = null;
        $this->isEncrypted = false;
        $this->errorMsg = null;
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->resultFilename = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->pagesProcessed = 0;
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'page-number-pdf';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 10;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.page-number-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
        ]);
    }
}
