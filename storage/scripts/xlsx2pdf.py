#!/usr/bin/env python3
"""
Excel to PDF Conversion Script using LibreOffice Headless
Supports converting .xlsx, .xls, .csv, and .ods to high-fidelity PDF documents.
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

def count_pdf_pages(pdf_path):
    """Count pages in the generated PDF using PyMuPDF (fitz) or pypdf."""
    try:
        import fitz
        with fitz.open(pdf_path) as doc:
            return len(doc)
    except Exception:
        pass

    try:
        from pypdf import PdfReader
        reader = PdfReader(pdf_path)
        return len(reader.pages)
    except Exception:
        pass

    return 1

def convert_excel_to_pdf(input_path, output_path):
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"Input file not found: {input_path}")

    soffice_bin = find_libreoffice()
    if not soffice_bin:
        raise RuntimeError("LibreOffice installation not found on server. Please install LibreOffice to enable Excel to PDF conversion.")

    original_size = os.path.getsize(input_path)
    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    # Use a temporary working directory and isolated LibreOffice profile to prevent concurrency conflicts
    with tempfile.TemporaryDirectory() as temp_dir:
        profile_dir = os.path.join(temp_dir, "libreoffice_profile")
        os.makedirs(profile_dir, exist_ok=True)
        profile_uri = "file:///" + profile_dir.replace("\\", "/")

        # Copy input file to temp directory to avoid locking original
        input_filename = os.path.basename(input_path)
        base_name, _ = os.path.splitext(input_filename)
        temp_input = os.path.join(temp_dir, input_filename)
        shutil.copy2(input_path, temp_input)

        # Expected output PDF filename from LibreOffice
        expected_pdf = os.path.join(temp_dir, f"{base_name}.pdf")

        # LibreOffice conversion command using calc_pdf_Export filter
        cmd = [
            soffice_bin,
            f"-env:UserInstallation={profile_uri}",
            "--headless",
            "--invisible",
            "--nocrashreport",
            "--nodefault",
            "--nofirststartwizard",
            "--nolockcheck",
            "--nologo",
            "--norestore",
            "--convert-to", "pdf:calc_pdf_Export",
            "--outdir", temp_dir,
            temp_input
        ]

        result = subprocess.run(
            cmd,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=120
        )

        if not os.path.exists(expected_pdf) or os.path.getsize(expected_pdf) == 0:
            err_msg = result.stderr.strip() or result.stdout.strip() or "LibreOffice failed to generate PDF output."
            raise RuntimeError(f"Conversion failed: {err_msg}")

        # Move the generated PDF to the destination path
        shutil.move(expected_pdf, output_path)

        new_size = os.path.getsize(output_path)
        page_count = count_pdf_pages(output_path)

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
            "message": "Usage: python xlsx2pdf.py <input_excel_path> <output_pdf_path>"
        }))
        sys.exit(1)

    input_path = os.path.abspath(sys.argv[1])
    output_path = os.path.abspath(sys.argv[2])

    try:
        res = convert_excel_to_pdf(input_path, output_path)
        print(json.dumps(res))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))
        sys.exit(1)

if __name__ == "__main__":
    main()
