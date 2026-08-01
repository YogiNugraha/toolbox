<?php
$cmd = '"C:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to docx --outdir "C:\laragon\www\toolbox\storage\app\private\private\temp" "C:\laragon\www\toolbox\storage\app\private\private\temp\4b20fce0-feaf-4b8b-9273-67ae58ba4676.pdf"';
echo "CMD: " . $cmd . "\n";
$output = shell_exec($cmd);
echo "Output: " . $output;
