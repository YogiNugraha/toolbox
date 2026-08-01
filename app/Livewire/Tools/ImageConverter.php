<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Tools\ImageProcessorService;
use Illuminate\Support\Facades\Storage;

class ImageConverter extends Component
{
    use WithFileUploads;

    public $file;
    public $outputFormat = 'png';
    public $errorMsg;
    
    public $resultPath;
    public $originalSize;
    public $newSize;
    public $resultExtension;

    public function updatedFile()
    {
        $this->resetResult();
        $this->validateFile();
        
        // Auto-select a different output format than the input
        if ($this->file) {
            $ext = strtolower($this->file->getClientOriginalExtension());
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $this->outputFormat = 'png';
            } elseif ($ext === 'png') {
                $this->outputFormat = 'jpg';
            } else {
                $this->outputFormat = 'jpg';
            }
        }
    }

    public function updatedOutputFormat()
    {
        $this->resetResult();
    }

    public function validateFile()
    {
        $this->errorMsg = null;
        $this->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,bmp|max:10240', // 10MB max
        ], [
            'file.mimes' => 'Format file tidak didukung.',
            'file.max' => 'Ukuran file maksimal 10MB.'
        ]);
    }

    public function convert(ImageProcessorService $processor)
    {
        $this->validateFile();

        if (!$this->file) {
            return;
        }

        try {
            // Quality 90 for conversion to preserve details
            $result = $processor->process($this->file, 90, null, $this->outputFormat);

            $this->resultPath = $result['path'];
            $this->originalSize = $result['original_size'];
            $this->newSize = $result['new_size'];
            $this->resultExtension = $result['extension'];

        } catch (\Exception $e) {
            $this->errorMsg = 'Gagal mengonversi gambar: ' . $e->getMessage();
        }
    }

    public function resetResult()
    {
        $this->resultPath = null;
        $this->originalSize = null;
        $this->newSize = null;
        $this->errorMsg = null;
    }

    public function download()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if ($this->resultPath && $disk->exists($this->resultPath)) {
            $filename = 'converted_' . time() . '.' . $this->resultExtension;
            return $disk->download($this->resultPath, $filename);
        }
        
        $this->errorMsg = 'File tidak ditemukan atau sudah kadaluarsa.';
    }

    public function render()
    {
        return view('livewire.tools.image-converter');
    }
}
