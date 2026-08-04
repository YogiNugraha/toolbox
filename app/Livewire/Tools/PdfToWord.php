<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ConvertPdfToWordJob;

class PdfToWord extends Component
{
    use WithFileUploads;

    public $file;
    public $jobId;
    public $status; // null, processing, completed, failed
    public $errorMsg;
    
    public $resultPath;
    public $originalSize;
    public $newSize;

    public function updatedFile()
    {
        $this->resetResult();
        $this->validateFile();
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        $this->validate([
            'file' => 'required|file|mimes:pdf|max:20480', // 20MB max
        ], [
            'file.mimes' => 'File harus berupa PDF.',
            'file.max' => 'Ukuran maksimal 20MB.'
        ]);

        if ($this->file) {
            $realMime = $this->file->getMimeType();
            if ($realMime !== 'application/pdf') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file' => 'Format file tidak valid. Ekstensi file mungkin dipalsukan.'
                ]);
            }
        }
    }

    public function convert()
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        try {
            $this->jobId = Str::uuid()->toString();
            $this->status = 'processing';
            
            // Store file temporarily
            $filename = $this->jobId . '.pdf';
            $userId = auth()->id() ?? 'guest';
            $relativePath = 'private/users/' . $userId . '/' . $filename;
            
            $inputPath = Storage::disk('local')->path($relativePath);
            $outputDir = Storage::disk('local')->path('private/users/' . $userId);
            
            // Move file to our persistent temp folder since getRealPath() will disappear after request
            $this->file->storeAs('private/users/' . $userId, $filename, 'local');
            
            $activity = \App\Models\Activity::create([
                'user_id' => auth()->id(),
                'tool_slug' => 'pdf-to-word',
                'original_filename' => $this->file->getClientOriginalName(),
                'original_size' => $this->file->getSize(),
                'status' => 'processing',
            ]);

            // Dispatch the job
            ConvertPdfToWordJob::dispatch($this->jobId, $inputPath, $outputDir, $activity->id);

        } catch (\Exception $e) {
            $this->status = 'failed';
            $this->errorMsg = 'Gagal memulai konversi: ' . $e->getMessage();
        }
    }

    public function checkStatus()
    {
        if ($this->status !== 'processing' || !$this->jobId) {
            return;
        }

        $currentStatus = Cache::get("pdf_conversion_status_{$this->jobId}");
        
        if ($currentStatus === 'completed') {
            $this->status = 'completed';
            $result = Cache::get("pdf_conversion_result_{$this->jobId}");
            if ($result) {
                $userId = auth()->id() ?? 'guest';
                $this->resultPath = 'private/users/' . $userId . '/' . pathinfo($result['path'], PATHINFO_BASENAME);
                $this->originalSize = $result['original_size'];
                $this->newSize = $result['new_size'];
            }
        } elseif ($currentStatus === 'failed') {
            $this->status = 'failed';
            $this->errorMsg = Cache::get("pdf_conversion_error_{$this->jobId}");
        }
    }

    public function download()
    {
        if ($this->resultPath && Storage::disk('local')->exists($this->resultPath)) {
            $filename = 'converted_' . time() . '.docx';
            
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('local');
            
            return $disk->download($this->resultPath, $filename);
        }
        
        $this->errorMsg = 'File hasil konversi tidak ditemukan.';
    }

    public function resetResult()
    {
        $this->jobId = null;
        $this->status = null;
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->errorMsg = null;
    }

    public function render()
    {
        return view('livewire.tools.pdf-to-word');
    }
}
