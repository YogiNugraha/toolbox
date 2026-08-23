#!/usr/bin/env python3
"""
Word to PDF Conversion Script using LibreOffice Headless
Supports converting .docx, .doc, .odt, and .rtf to PDF with full fidelity.
"""

import sys
import os
import shutil
import subprocess
import tempfile
import json
import site

# Dynamically add all potential site-packages directories (supports Apache/webserver runner)
_potential_site_dirs = [
    os.path.expanduser(r"~\AppData\Roaming\Python\Python313\site-packages"),
    r"C:\Users\Yogi Nugraha\AppData\Roaming\Python\Python313\site-packages",
    r"C:\Python313\Lib\site-packages",
    r"C:\laragon\bin\python\python-3.10\Lib\site-packages",
]

for _dir in _potential_site_dirs:
    if os.path.exists(_dir) and _dir not in sys.path:
        site.addsitedir(_dir)

def find_libreoffice():
    """Find the LibreOffice console or GUI binary."""
    potential_paths = [
        r"C:\Program Files\LibreOffice\program\soffice.com",
        r"C:\Program Files (x86)\LibreOffice\program\soffice.com",
        r"C:\Program Files\LibreOffice\program\soffice.exe",
        r"C:\Program Files (x86)\LibreOffice\program\soffice.exe",
        shutil.which("soffice.com"),
        shutil.which("soffice.exe"),
        shutil.which("soffice"),
        shutil.which("libreoffice"),
    ]
    for path in potential_paths:
        if path and os.path.exists(path):
            return path
    return None

def convert_word_to_pdf(input_path, output_path):
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"Input file not found: {input_path}")

    soffice_bin = find_libreoffice()
    if not soffice_bin:
        raise RuntimeError("LibreOffice installation not found on server. Please install LibreOffice to enable Word to PDF conversion.")

    original_size = os.path.getsize(input_path)
    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    # Use a temporary working directory and isolated LibreOffice profile to prevent concurrency conflicts
    with tempfile.TemporaryDirectory() as temp_dir:
        profile_dir = os.path.join(temp_dir, "libreoffice_profile")
        os.makedirs(profile_dir, exist_ok=True)
        # Convert path to file URI for -env:UserInstallation
        profile_uri = "file:///" + profile_dir.replace("\\", "/")

        # Copy input to temp dir to avoid file permission or special character path issues
        temp_input = os.path.join(temp_dir, os.path.basename(input_path))
        shutil.copy2(input_path, temp_input)

        cmd = [
            soffice_bin,
            "--headless",
            "--invisible",
            "--nodefault",
            "--nofirststartwizard",
            f"-env:UserInstallation={profile_uri}",
            "--convert-to",
            "pdf:writer_pdf_Export",
            temp_input,
            "--outdir",
            temp_dir
        ]

        result = subprocess.run(
            cmd,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=120
        )

        expected_filename = os.path.splitext(os.path.basename(temp_input))[0] + ".pdf"
        temp_pdf = os.path.join(temp_dir, expected_filename)

        if not os.path.exists(temp_pdf):
            # Check if any .pdf file was generated in temp_dir
            generated_pdfs = [f for f in os.listdir(temp_dir) if f.endswith(".pdf")]
            if generated_pdfs:
                temp_pdf = os.path.join(temp_dir, generated_pdfs[0])
            else:
                err_msg = result.stderr.strip() or result.stdout.strip() or "LibreOffice failed to generate PDF document."
                raise RuntimeError(f"Conversion failed: {err_msg}")

        # Move to target output path
        shutil.move(temp_pdf, output_path)

    new_size = os.path.getsize(output_path)
    
    # Calculate page count with PyMuPDF if available
    page_count = 1
    try:
        import pymupdf as fitz
        doc = fitz.open(output_path)
        page_count = len(doc)
        doc.close()
    except Exception:
        pass

    return {
        "status": "success",
        "original_size": original_size,
        "new_size": new_size,
        "page_count": page_count,
        "output_path": output_path
    }

def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python word2pdf.py <input_path> <output_path>"
        }))
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    try:
        result = convert_word_to_pdf(input_path, output_path)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))
        sys.exit(1)

if __name__ == "__main__":
    main()
