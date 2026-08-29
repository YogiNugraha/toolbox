<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\JpgToPdfService;
use App\Services\EntitlementService;
use App\Models\Activity;
use App\Traits\LivewireLineoneAlerts;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class JpgToPdf extends Component
{
    use WithFileUploads, LivewireLineoneAlerts;

    public $images = [];
    public $layout = 'a4_auto'; // 'a4_auto', 'a4_portrait', 'a4_landscape', 'fit'
    public $margin = 'small'; // 'none', 'small', 'normal'

    // Output results state
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $pageCount;
    public $imagesCount;
    public $resultFilename;
    public $errorMsg;

    public function updatedImages()
    {
        $this->resetResult();
        $this->validateImages();
    }

    public function validateImages()
    {
        $this->errorMsg = null;
        try {
            $this->validate([
                'images' => 'required|array|min:1',
                'images.*' => 'required|file|image|mimes:jpeg,jpg,png,webp,bmp|max:20480', // 20MB each
            ], [
                'images.min' => 'Pilih minimal 1 file gambar untuk dikonversi ke PDF.',
                'images.*.image' => 'Format file harus berupa gambar yang valid (JPG, PNG, WebP).',
                'images.*.mimes' => 'Format file harus berupa JPG, PNG, WebP, atau BMP.',
                'images.*.max' => 'Ukuran setiap gambar maksimal 20MB.'
            ]);
        } catch (ValidationException $e) {
            $this->toast('Format gambar tidak valid atau melebihi batas ukuran.', 'error');
            throw $e;
        }
    }

    public function moveUp($index)
    {
        if ($index > 0 && isset($this->images[$index]) && isset($this->images[$index - 1])) {
            $temp = $this->images[$index - 1];
            $this->images[$index - 1] = $this->images[$index];
            $this->images[$index] = $temp;
            $this->resetResult();
        }
    }

    public function moveDown($index)
    {
        if ($index < count($this->images) - 1 && isset($this->images[$index]) && isset($this->images[$index + 1])) {
            $temp = $this->images[$index + 1];
            $this->images[$index + 1] = $this->images[$index];
            $this->images[$index] = $temp;
            $this->resetResult();
        }
    }

    public function removeImage($index)
    {
        if (isset($this->images[$index])) {
            array_splice($this->images, $index, 1);
            $this->resetResult();
        }
    }

    public function resetImages()
    {
        $this->images = [];
        $this->resetResult();
        $this->resetValidation();
        $this->resetErrorBag();
        $this->errorMsg = null;
    }

    public function convert(JpgToPdfService $converter, EntitlementService $entitlementService)
    {
        $this->validateImages();

        if (empty($this->images)) {
            $this->errorMsg = 'Pilih minimal 1 gambar untuk dikonversi ke PDF.';
            return;
        }

        $user = auth()->user();
        $toolSlug = 'jpg-to-pdf';

        // 1. Quota check
        $remaining = $entitlementService->getRemainingQuota($user, $toolSlug);
        if ($remaining !== null && $remaining <= 0) {
            $this->errorMsg = 'Kuota bulanan Anda sudah habis. Silakan upgrade ke paket Pro untuk akses tanpa batas.';
            $this->toast('Kuota bulanan kamu sudah habis.', 'info');
            return;
        }

        try {
            $totalSize = array_sum(array_map(fn($f) => $f->getSize(), $this->images));

            $activity = Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => $toolSlug,
                'original_filename' => count($this->images) > 1 
                    ? (count($this->images) . ' Gambar (' . $this->images[0]->getClientOriginalName() . ' dkk)') 
                    : $this->images[0]->getClientOriginalName(),
                'original_size' => $totalSize,
                'status' => 'processing',
            ]);

            $result = $converter->convert($this->images, $this->layout, $this->margin);

            $this->resultPath = $result['result_path'];
            $this->resultFilename = $result['result_filename'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->pageCount = $result['page_count'];
            $this->imagesCount = $result['images_count'];

            $activity->update([
                'result_filename' => $this->resultFilename,
                'result_size' => $this->newSize,
                'result_path' => $this->resultPath,
                'status' => 'completed',
                'meta' => [
                    'images_count' => $this->imagesCount,
                    'page_count' => $this->pageCount,
                    'layout' => $this->layout,
                    'margin' => $this->margin,
                ],
            ]);

            \App\Models\Tool::incrementUsage($toolSlug);

            $this->toast('Gambar berhasil dikonversi ke dokumen PDF!', 'success');

        } catch (\Exception $e) {
            if (isset($activity)) {
                $activity->update([
                    'status' => 'failed',
                    'meta' => ['error' => $e->getMessage()]
                ]);
            }
            $this->errorMsg = 'Gagal mengonversi gambar ke PDF: ' . $e->getMessage();
            $this->toast('Konversi gagal.', 'error');
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->pageCount = null;
        $this->imagesCount = null;
        $this->resultFilename = null;
        $this->errorMsg = null;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = $this->resultFilename ?: ('converted_document_' . time() . '.pdf');
            return Storage::disk('local')->download($this->resultPath, $filename);
        }

        if ($this->resultPath && file_exists(storage_path('app/' . $this->resultPath))) {
            $filename = $this->resultFilename ?: ('converted_document_' . time() . '.pdf');
            return response()->download(storage_path('app/' . $this->resultPath), $filename);
        }

        $this->errorMsg = 'File dokumen PDF tidak ditemukan atau sesi telah berakhir.';
        $this->toast('File tidak ditemukan.', 'error');
    }

    public function render(EntitlementService $entitlementService)
    {
        $user = auth()->user();
        $toolSlug = 'jpg-to-pdf';

        $remainingQuota = $entitlementService->getRemainingQuota($user, $toolSlug);
        $currentPlan = $entitlementService->getCurrentPlan($user);
        $monthlyLimit = $currentPlan->limits[$toolSlug]['monthly_quota'] ?? $currentPlan->limits[$toolSlug]['daily_quota'] ?? 5;
        $maxMb = $currentPlan->limits[$toolSlug]['max_file_size_mb'] ?? 20;
        $isPro = $user && $user->isSubscribed();

        return view('livewire.tools.jpg-to-pdf', [
            'remainingQuota' => $remainingQuota,
            'monthlyLimit' => $monthlyLimit,
            'maxMb' => $maxMb,
            'isPro' => $isPro,
            'currentPlan' => $currentPlan,
            'errorMsg' => $this->errorMsg,
        ]);
    }
}
