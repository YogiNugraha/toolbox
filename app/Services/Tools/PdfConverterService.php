<?php

namespace App\Services\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

class PdfConverterService
{
    /**
     * Converts a PDF to a Word document using LibreOffice headless.
     *
     * @param string $inputPath The absolute path to the input PDF file.
     * @param string $outputDir The directory to store the output Word file.
     * @return string The path to the generated Word document.
     * @throws \Exception
     */
    public function convertPdfToWord(string $inputPath, string $outputDir): string
    {
        // We use Python's pdf2docx library instead of LibreOffice because LibreOffice Draw does not natively support exporting PDFs to DOCX.
        $pythonScript = base_path('storage/scripts/pdf2word.py');
        $pythonExecutable = config('services.python_path', 'python');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Python script not found at: {$pythonScript}");
        }

        $process = new Process([
            $pythonExecutable,
            $pythonScript,
            $inputPath,
            $outputDir,
        ]);
        
        // Conversion can take some time for large PDFs
        $process->setTimeout(300);

        try {
            $process->mustRun();
            
            // Extract the original filename without extension to determine output filename
            $filenameWithoutExt = pathinfo($inputPath, PATHINFO_FILENAME);
            $outputFile = $outputDir . DIRECTORY_SEPARATOR . $filenameWithoutExt . '.docx';

            if (!file_exists($outputFile)) {
                throw new \Exception("Conversion finished but output file not found: {$outputFile}");
            }

            return $outputFile;

        } catch (ProcessFailedException $e) {
            throw new \Exception("PDF to Word conversion failed: " . $e->getMessage());
        }
    }
}
