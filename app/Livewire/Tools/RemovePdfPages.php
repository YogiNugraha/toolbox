<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\RemovePdfPagesService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;

class RemovePdfPages extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $pagesInput = '';
    public $selectedPages = [];
    public $totalPages = 0;
    public $thumbBatch;
    public $thumbnailsCount = 0;
    public $isEncrypted = false;

    // Output Result State
    public $resultPath;
    public $resultFilename;
    public $originalSize;
    public $newSize;
    public $totalPagesBefore;
    public $totalPagesAfter;
    public $removedCount;
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
        $this->selectedPages = [];
        $this->pagesInput = '';

        try {
            $service = app(RemovePdfPagesService::class);
            if ($this->thumbBatch) {
                $service->cleanupThumbnails($this->thumbBatch);
                $this->thumbBatch = null;
            }

            $inspect = $service->inspect($this->file);

            if (!empty($inspect['is_encrypted']) && !empty($inspect['needs_pass'])) {
                $this->errorMsg = 'Dokumen PDF ini diproteksi password. Silakan buka proteksinya terlebih dahulu di tool Buka Proteksi PDF.';
                $this->isEncrypted = true;
                $this->toast('PDF terproteksi kata sandi.', 'warning');
                return;
            }

            $this->totalPages = $inspect['total_pages'] ?? 1;
            $thumbData = $service->generateThumbnails($this->file, 36);
            $this->thumbBatch = $thumbData['batch_id'] ?? null;
            $this->thumbnailsCount = $thumbData['count'] ?? 0;

        } catch (\Throwable $e) {
            $this->errorMsg = 'Gagal memeriksa dokumen: ' . $e->getMessage();
            $this->toast('Gagal memvalidasi PDF.', 'error');
        }
    }

    public function togglePage(int $page)
    {
        if (in_array($page, $this->selectedPages)) {
            $this->selectedPages = array_values(array_diff($this->selectedPages, [$page]));
        } else {
            if (count($this->selectedPages) >= ($this->totalPages - 1)) {
                $this->toast('Dokumen PDF harus menyisakan setidaknya 1 halaman.', 'warning');
                return;
            }
            $this->selectedPages[] = $page;
            sort($this->selectedPages);
        }

        $this->syncInputFromSelected();
    }

    public function updatedPagesInput()
    {
        $parsed = $this->parseRangeString($this->pagesInput, $this->totalPages);

        if (count($parsed) >= $this->totalPages && $this->totalPages > 0) {
            $this->toast('Tidak dapat menghapus seluruh halaman. Harus menyisakan minimal 1 halaman.', 'warning');
            $parsed = array_slice($parsed, 0, $this->totalPages - 1);
        }

        $this->selectedPages = $parsed;
    }

    public function selectOdd()
    {
        $odd = [];
        for ($i = 1; $i <= $this->totalPages; $i += 2) {
            $odd[] = $i;
        }

        if (count($odd) >= $this->totalPages) {
            array_pop($odd);
        }

        $this->selectedPages = $odd;
        $this->syncInputFromSelected();
        $this->toast('Semua halaman ganjil ditandai untuk dihapus.', 'info');
    }

    public function selectEven()
    {
        $even = [];
        for ($i = 2; $i <= $this->totalPages; $i += 2) {
            $even[] = $i;
        }

        if (count($even) >= $this->totalPages) {
            array_pop($even);
        }

        $this->selectedPages = $even;
        $this->syncInputFromSelected();
        $this->toast('Semua halaman genap ditandai untuk dihapus.', 'info');
    }

    public function clearSelection()
    {
        $this->selectedPages = [];
        $this->pagesInput = '';
        $this->toast('Pilihan halaman dibatalkan.', 'info');
    }

    protected function syncInputFromSelected()
    {
        if (empty($this->selectedPages)) {
            $this->pagesInput = '';
            return;
        }

        sort($this->selectedPages);
        $ranges = [];
        $count = count($this->selectedPages);

        for ($i = 0; $i < $count; $i++) {
            $start = $this->selectedPages[$i];
            while ($i + 1 < $count && $this->selectedPages[$i + 1] === $this->selectedPages[$i] + 1) {
                $i++;
            }
            $end = $this->selectedPages[$i];

            if ($start === $end) {
                $ranges[] = (string) $start;
            } else {
                $ranges[] = "{$start}-{$end}";
            }
        }

        $this->pagesInput = implode(', ', $ranges);
    }

    protected function parseRangeString(string $rangeStr, int $maxPages): array
    {
        $pages = [];
        $parts = preg_split('/[,;\s]+/', trim($rangeStr), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            if (str_contains($part, '-')) {
                $bounds = explode('-', $part);
                if (count($bounds) === 2 && is_numeric($bounds[0]) && is_numeric($bounds[1])) {
                    $start = max(1, (int) $bounds[0]);
                    $end = min($maxPages, (int) $bounds[1]);
                    if ($start <= $end) {
                        for ($p = $start; $p <= $end; $p++) {
                            $pages[] = $p;
                        }
                    }
                }
            } elseif (is_numeric($part)) {
                $val = (int) $part;
                if ($val >= 1 && $val <= $maxPages) {
                    $pages[] = $val;
                }
            }
        }

        $unique = array_unique($pages);
        sort($unique);
        return $unique;
    }

    public function process(RemovePdfPagesService $service, EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'remove-pdf-pages';

        if (!$this->file) {
            $this->errorMsg = 'Silakan pilih dokumen PDF terlebih dahulu.';
            $this->toast('Dokumen PDF belum dipilih.', 'warning');
            return;
        }

        if (empty($this->selectedPages)) {
            $this->errorMsg = 'Silakan pilih setidaknya satu halaman yang ingin dihapus.';
            $this->toast('Pilih halaman yang ingin dibuang.', 'warning');
            return;
        }

        if (count($this->selectedPages) >= $this->totalPages) {
            $this->errorMsg = 'Dokumen PDF harus menyisakan setidaknya 1 halaman.';
            $this->toast('Tidak dapat menghapus seluruh halaman.', 'warning');
            return;
        }

        // Quota check
        $remaining = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota bulanan kamu sudah habis.', 'info');
            return;
        }

        try {
            $pagesStr = implode(', ', $this->selectedPages);

            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            $result = $service->removePages($this->file, $pagesStr);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->totalPagesBefore = $result['total_pages_before'];
            $this->totalPagesAfter = $result['total_pages_after'];
            $this->removedCount = $result['removed_count'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'removed_count' => $this->removedCount,
                    'pages_after' => $this->totalPagesAfter,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast("Berhasil menghapus {$this->removedCount} halaman!", 'success');

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
            $filename = $this->resultFilename ?: 'dokumen_hasil.pdf';
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        $this->errorMsg = 'File hasil tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function resetFile()
    {
        if ($this->thumbBatch) {
            app(RemovePdfPagesService::class)->cleanupThumbnails($this->thumbBatch);
        }
        $this->file = null;
        $this->resetResult();
        $this->totalPages = 0;
        $this->thumbBatch = null;
        $this->thumbnailsCount = 0;
        $this->selectedPages = [];
        $this->pagesInput = '';
        $this->isEncrypted = false;
        $this->errorMsg = null;
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->resultFilename = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->totalPagesBefore = null;
        $this->totalPagesAfter = null;
        $this->removedCount = null;
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'remove-pdf-pages';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 10;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.remove-pdf-pages', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
        ]);
    }
}
