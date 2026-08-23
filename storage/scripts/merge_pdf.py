#!/usr/bin/env python3
"""
Merge Multiple PDF Files into One Unified PDF Document.
Fast, lossless vector & typography preservation.
"""

import sys
import os
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

def merge_pdfs(input_paths, output_path):
    if not input_paths or len(input_paths) < 2:
        raise ValueError("Setidaknya diperlukan 2 file PDF untuk digabungkan.")

    total_original_size = 0
    valid_files = []

    for path in input_paths:
        if not os.path.exists(path):
            raise FileNotFoundError(f"File PDF tidak ditemukan: {path}")
        total_original_size += os.path.getsize(path)
        valid_files.append(path)

    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    # Strategy 1: PyMuPDF (fitz) - High Performance & Lossless
    try:
        import fitz
        merged_doc = fitz.open()

        for pdf_path in valid_files:
            src_doc = fitz.open(pdf_path)
            merged_doc.insert_pdf(src_doc)
            src_doc.close()

        merged_doc.save(output_path, deflate=True, garbage=3)
        total_pages = len(merged_doc)
        merged_doc.close()

        if not os.path.exists(output_path) or os.path.getsize(output_path) == 0:
            raise RuntimeError("Gagal menghasilkan file PDF gabungan yang valid.")

        return {
            "status": "success",
            "files_count": len(valid_files),
            "page_count": total_pages,
            "original_size": total_original_size,
            "new_size": os.path.getsize(output_path),
            "output_path": output_path,
            "engine": "pymupdf"
        }
    except Exception as e_fitz:
        # Strategy 2: pypdf fallback
        try:
            from pypdf import PdfMerger
            merger = PdfMerger()
            for pdf_path in valid_files:
                merger.append(pdf_path)
            
            with open(output_path, "wb") as f_out:
                merger.write(f_out)
            merger.close()

            import fitz
            with fitz.open(output_path) as check_doc:
                total_pages = len(check_doc)

            return {
                "status": "success",
                "files_count": len(valid_files),
                "page_count": total_pages,
                "original_size": total_original_size,
                "new_size": os.path.getsize(output_path),
                "output_path": output_path,
                "engine": "pypdf"
            }
        except Exception as e_pypdf:
            raise RuntimeError(f"Gagal menggabungkan PDF: {str(e_fitz)} (Fallback error: {str(e_pypdf)})")

def main():
    # Supports JSON string of args or standard CLI arguments
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python merge_pdf.py <output_pdf_path> <input_pdf_1> <input_pdf_2> ..."
        }))
        sys.exit(1)

    output_path = os.path.abspath(sys.argv[1])
    input_paths = [os.path.abspath(p) for p in sys.argv[2:]]

    try:
        res = merge_pdfs(input_paths, output_path)
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
