<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\WordCounterService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;

class WordCounter extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $text = '';
    public $fileUpload;
    public $stats = [
        'words' => 0,
        'characters_with_spaces' => 0,
        'characters_no_spaces' => 0,
        'sentences' => 0,
        'paragraphs' => 0,
        'reading_time' => '0 dtk',
        'speaking_time' => '0 dtk',
    ];

    public $undoStack = [];
    public $errorMsg;

    public function mount(WordCounterService $service)
    {
        $this->updateStats($service);
    }

    public function updatedText()
    {
        $service = app(WordCounterService::class);
        $this->updateStats($service);
    }

    public function updatedFileUpload()
    {
        $this->validate([
            'fileUpload' => 'file|mimes:txt,csv,md,json|max:5120',
        ], [
            'fileUpload.file' => 'File harus berupa dokumen teks (TXT, MD, CSV).',
            'fileUpload.mimes' => 'Format file yang didukung: .txt, .md, .csv, .json.',
            'fileUpload.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $content = file_get_contents($this->fileUpload->getRealPath());
            if ($content !== false) {
                // Push current text to undo stack
                if (trim($this->text) !== '') {
                    $this->pushUndo($this->text);
                }
                $this->text = $content;
                $service = app(WordCounterService::class);
                $this->updateStats($service);
                $this->toast('Isi file berhasil dimuat ke editor!', 'success');
            }
        } catch (\Throwable $e) {
            $this->toast('Gagal membaca isi file: ' . $e->getMessage(), 'error');
        }

        $this->fileUpload = null;
    }

    public function updateStats(WordCounterService $service)
    {
        $this->stats = $service->analyze($this->text);
    }

    public function transform(string $mode, WordCounterService $service)
    {
        if (trim($this->text) === '') {
            $this->toast('Teks masih kosong. Masukkan teks terlebih dahulu.', 'warning');
            return;
        }

        $this->pushUndo($this->text);
        $this->text = $service->convertCase($this->text, $mode);
        $this->updateStats($service);

        $label = match ($mode) {
            'upper' => 'HURUF BESAR',
            'lower' => 'huruf kecil',
            'title' => 'Title Case',
            'sentence' => 'Sentence case',
            'camel' => 'camelCase',
            'snake' => 'snake_case',
            'kebab' => 'kebab-case',
            'clean_spaces' => 'Spasi berlebih dibersihkan',
            'clean_empty_lines' => 'Baris kosong dibersihkan',
            default => 'Format diubah',
        };

        $this->toast("Berhasil diubah ke {$label}!", 'success');
    }

    public function undo(WordCounterService $service)
    {
        if (!empty($this->undoStack)) {
            $previous = array_pop($this->undoStack);
            $this->text = $previous;
            $this->updateStats($service);
            $this->toast('Perubahan terakhir berhasil dibatalkan (Undo).', 'info');
        } else {
            $this->toast('Tidak ada riwayat untuk dibatalkan.', 'info');
        }
    }

    protected function pushUndo(string $text)
    {
        if (count($this->undoStack) >= 10) {
            array_shift($this->undoStack);
        }
        $this->undoStack[] = $text;
    }

    public function clearText(WordCounterService $service)
    {
        if (trim($this->text) !== '') {
            $this->pushUndo($this->text);
            $this->text = '';
            $this->updateStats($service);
            $this->toast('Editor teks berhasil dikosongkan.', 'info');
        }
    }

    public function downloadTxt(WordCounterService $service, EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'word-counter';

        if (trim($this->text) === '') {
            $this->toast('Tidak ada teks untuk diunduh.', 'warning');
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
            $export = $service->exportTxt($this->text);

            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => 'dokumen_teks.txt',
                'original_size' => strlen($this->text),
                'result_filename' => $export['result_filename'],
                'result_size' => $export['size'],
                'result_path' => $export['result_path'],
                'status' => 'completed',
                'meta' => [
                    'words' => $this->stats['words'],
                    'chars' => $this->stats['characters_with_spaces'],
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('File teks siap diunduh!', 'success');

            return Storage::disk('local')->download($export['result_path'], $export['result_filename']);

        } catch (\Throwable $e) {
            $this->errorMsg = 'Gagal mengekspor file: ' . $e->getMessage();
            $this->toast('Gagal mengekspor file.', 'error');
        }
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'word-counter';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 30;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.word-counter', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'canUndo' => !empty($this->undoStack),
        ]);
    }
}
