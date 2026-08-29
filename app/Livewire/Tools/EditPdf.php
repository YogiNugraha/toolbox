<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\EditPdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditPdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $detectedPages = 0;
    public $currentPage = 1;
    public $currentPageImage;
    public $pageDimensions = ['width' => 800, 'height' => 1100];

    // Output results state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $originalPages;
    public $finalPages;
    public $resultFilename;
    public $errorMsg;

    public function updatedFile(EditPdfService $editor)
    {
        $this->resetResult();
        if (! $this->file) {
            $this->resetValidation();
            $this->resetErrorBag();
            $this->currentPageImage = null;
            $this->detectedPages = 0;
            $this->currentPage = 1;
            return;
        }
        $this->validateFile();

        try {
            $renderData = $editor->renderPage($this->file, 1, 130);
            $this->currentPage = 1;
            $this->detectedPages = $renderData['total_pages'] ?? 1;
            $this->currentPageImage = $renderData['image'] ?? null;
            $this->pageDimensions = [
                'width' => $renderData['width'] ?? 800,
                'height' => $renderData['height'] ?? 1100,
            ];

            $this->dispatch('pdf-loaded', [
                'page' => 1,
                'totalPages' => $this->detectedPages,
                'image' => $this->currentPageImage,
                'width' => $this->pageDimensions['width'],
                'height' => $this->pageDimensions['height'],
                'fileName' => $this->file->getClientOriginalName(),
                'fileSize' => $this->file->getSize()
            ]);

        } catch (\Throwable $e) {
            $this->errorMsg = 'Gagal memuat dokumen PDF: ' . $e->getMessage();
            $this->toast('Gagal memuat PDF.', 'error');
        }
    }

    public function loadPage($pageNum, EditPdfService $editor)
    {
        if (!$this->file) return;
        
        try {
            $renderData = $editor->renderPage($this->file, (int)$pageNum, 130);
            $this->currentPage = (int)$pageNum;
            $this->currentPageImage = $renderData['image'] ?? null;
            $this->pageDimensions = [
                'width' => $renderData['width'] ?? 800,
                'height' => $renderData['height'] ?? 1100,
            ];

            $this->dispatch('page-rendered', [
                'page' => $this->currentPage,
                'totalPages' => $this->detectedPages,
                'image' => $this->currentPageImage,
                'width' => $this->pageDimensions['width'],
                'height' => $this->pageDimensions['height'],
            ]);
        } catch (\Throwable $e) {
            $this->toast('Gagal memuat halaman ' . $pageNum, 'error');
        }
    }

    public function resetFile()
    {
        $this->file = null;
        $this->currentPageImage = null;
        $this->detectedPages = 0;
        $this->currentPage = 1;
        $this->resetResult();
        $this->resetValidation();
        $this->resetErrorBag();
        $this->errorMsg = null;
        $this->dispatch('pdf-reset');
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
        } catch (ValidationException $e) {
            $this->toast('Format file tidak didukung atau melebihi batas ukuran.', 'error');
            throw $e;
        }
    }

    /**
     * Save the edited PDF using the canvas overlay layers from the visual studio.
     */
    public function saveEditedPdf($payload, EditPdfService $editor, EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            $this->toast('Dokumen PDF tidak ditemukan.', 'error');
            return;
        }

        $user = auth()->user();
        $toolSlug = 'edit-pdf';

        // 1. Quota check
        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota bulanan kamu sudah habis.', 'info');
            return;
        }

        $options = [
            'page_overlays' => $payload['overlays'] ?? [],
            'page_rotations' => $payload['rotations'] ?? [],
            'deleted_pages' => $payload['deletedPages'] ?? [],
            'metadata' => $payload['metadata'] ?? [],
        ];

        try {
            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            $result = $editor->edit($this->file, $options);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->originalPages = $result['original_pages'];
            $this->finalPages = $result['final_pages'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'original_pages' => $this->originalPages,
                    'final_pages' => $this->finalPages,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Dokumen PDF berhasil diedit & disimpan!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal mengedit PDF: ' . $e->getMessage();
            $this->toast('Edit PDF gagal: ' . $e->getMessage(), 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->originalPages = null;
        $this->finalPages = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('edited_document_' . time() . '.pdf');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('edited_document_' . time() . '.pdf');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File PDF hasil edit tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'edit-pdf';

        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.edit-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
