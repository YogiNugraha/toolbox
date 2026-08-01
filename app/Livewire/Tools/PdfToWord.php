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
            $relativePath = 'private/temp/' . $filename;
            
            $inputPath = Storage::disk('local')->path($relativePath);
            $outputDir = Storage::disk('local')->path('private/temp');
            
            // Move file to our persistent temp folder since getRealPath() will disappear after request
            $this->file->storeAs('private/temp', $filename, 'local');
            
            // Dispatch the job
            ConvertPdfToWordJob::dispatch($this->jobId, $inputPath, $outputDir);

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
                // $result['path'] is absolute path, we need relative path for Storage facade if needed, 
                // but since download can use absolute path or we can parse it.
                // It's in storage/app/private/temp/
                $this->resultPath = 'private/temp/' . pathinfo($result['path'], PATHINFO_BASENAME);
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
