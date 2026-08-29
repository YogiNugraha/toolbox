<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\RotatePdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RotatePdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $file;
    public $detectedPages = 0;
    public $pages = []; // [['page' => 1, 'thumbnail' => '...', 'rotation' => 0], ...]
    public $rotations = []; // ['1' => 90, '2' => 0, ...]
    public $globalRotation = 0;

    // Output results state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $totalPages;
    public $rotatedPages;
    public $resultFilename;
    public $errorMsg;

    public function updatedFile(RotatePdfService $rotator)
    {
        $this->resetResult();
        if (!$this->file) {
            $this->resetValidation();
            $this->resetErrorBag();
            $this->detectedPages = 0;
            $this->pages = [];
            $this->rotations = [];
            return;
        }
        $this->validateFile();

        try {
            $thumbnailData = $rotator->renderThumbnails($this->file, 65);
            $this->detectedPages = $thumbnailData['total_pages'] ?? 1;
            
            $this->pages = [];
            $this->rotations = [];

            foreach ($thumbnailData['thumbnails'] as $thumb) {
                $pNum = $thumb['page'];
                $this->pages[] = [
                    'page' => $pNum,
                    'thumbnail' => $thumb['image'],
                    'width' => $thumb['width'],
                    'height' => $thumb['height'],
                    'rotation' => 0,
                ];
                $this->rotations[(string)$pNum] = 0;
            }

        } catch (\Throwable $e) {
            $this->errorMsg = 'Gagal memuat dokumen PDF: ' . $e->getMessage();
            $this->toast('Gagal memuat PDF.', 'error');
        }
    }

    public function rotatePage($pageNum, $direction = 'right')
    {
        $delta = ($direction === 'left') ? -90 : 90;
        $pageKey = (string)$pageNum;

        $current = $this->rotations[$pageKey] ?? 0;
        $newRot = ($current + $delta) % 360;
        if ($newRot < 0) {
            $newRot += 360;
        }

        $this->rotations[$pageKey] = $newRot;

        // Update pages array
        foreach ($this->pages as &$p) {
            if ($p['page'] == $pageNum) {
                $p['rotation'] = $newRot;
                break;
            }
        }
    }

    public function rotateAll($direction = 'right')
    {
        $delta = ($direction === 'left') ? -90 : 90;

        foreach ($this->pages as &$p) {
            $newRot = ($p['rotation'] + $delta) % 360;
            if ($newRot < 0) {
                $newRot += 360;
            }
            $p['rotation'] = $newRot;
            $this->rotations[(string)$p['page']] = $newRot;
        }
    }

    public function setAllAngle($angle = 90)
    {
        $angle = ((int)$angle) % 360;
        if ($angle < 0) $angle += 360;

        foreach ($this->pages as &$p) {
            $p['rotation'] = $angle;
            $this->rotations[(string)$p['page']] = $angle;
        }
    }

    public function resetAllRotations()
    {
        foreach ($this->pages as &$p) {
            $p['rotation'] = 0;
            $this->rotations[(string)$p['page']] = 0;
        }
    }

    public function resetFile()
    {
        $this->file = null;
        $this->detectedPages = 0;
        $this->pages = [];
        $this->rotations = [];
        $this->globalRotation = 0;
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
        } catch (ValidationException $e) {
            $this->toast('Format file tidak didukung atau melebihi batas ukuran.', 'error');
            throw $e;
        }
    }

    public function rotate(RotatePdfService $rotator, EntitlementService $entitlementService)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        $user = auth()->user();
        $toolSlug = 'rotate-pdf';

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

            $result = $rotator->rotate($this->file, $this->rotations, $this->globalRotation);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->totalPages = $result['total_pages'];
            $this->rotatedPages = $result['rotated_pages'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'total_pages' => $this->totalPages,
                    'rotated_pages' => $this->rotatedPages,
                    'rotations' => $this->rotations,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Dokumen PDF berhasil diputar!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal memutar PDF: ' . $e->getMessage();
            $this->toast('Proses rotasi gagal: ' . $e->getMessage(), 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->totalPages = null;
        $this->rotatedPages = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('rotated_document_' . time() . '.pdf');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('rotated_document_' . time() . '.pdf');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File PDF hasil rotasi tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'rotate-pdf';

        $remainingQuota = $user ? $entitlementService->getRemainingQuota($user, $toolSlug) : null;
        $currentPlan = $user ? $entitlementService->getCurrentPlan($user) : \App\Models\Plan::where('is_default', true)->first();
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 50;
        $isPro = $user && $user->isSubscribed();

        // Calculate count of pages with non-zero rotation
        $activeRotationsCount = 0;
        foreach ($this->rotations as $rot) {
            if ($rot % 360 !== 0) {
                $activeRotationsCount++;
            }
        }

        return view('livewire.tools.rotate-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
            'activeRotationsCount' => $activeRotationsCount,
        ]);
    }
}
