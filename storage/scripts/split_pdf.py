#!/usr/bin/env python3
"""
Split PDF Document into Separate Pages (ZIP) or Extract Specific Page Ranges (PDF).
Ultra-fast lossless vector processing.
"""

import sys
import os
import re
import json
import zipfile
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

def parse_page_range(range_str, max_pages):
    """
    Parse a range string like '1-3, 5, 8-10' into sorted list of unique 1-indexed integers.
    """
    pages = set()
    parts = re.split(r'[,;\s]+', str(range_str).strip())
    
    for part in parts:
        if not part:
            continue
        if '-' in part:
            bounds = part.split('-')
            if len(bounds) == 2 and bounds[0].isdigit() and bounds[1].isdigit():
                start = max(1, int(bounds[0]))
                end = min(max_pages, int(bounds[1]))
                if start <= end:
                    for p in range(start, end + 1):
                        pages.add(p)
        elif part.isdigit():
            val = int(part)
            if 1 <= val <= max_pages:
                pages.add(val)
                
    return sorted(list(pages))

def count_pages(pdf_path):
    import fitz
    with fitz.open(pdf_path) as doc:
        return len(doc)

def split_pdf(input_pdf, output_path, mode="all", page_range_str=""):
    if not os.path.exists(input_pdf):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_pdf}")

    import fitz
    doc = fitz.open(input_pdf)
    total_original_pages = len(doc)
    original_size = os.path.getsize(input_pdf)

    if total_original_pages == 0:
        doc.close()
        raise ValueError("Dokumen PDF tidak memiliki halaman.")

    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    base_name = os.path.splitext(os.path.basename(input_pdf))[0]

    if mode == "range" and page_range_str:
        # Extract selected page range into a single unified PDF
        selected_pages = parse_page_range(page_range_str, total_original_pages)
        if not selected_pages:
            doc.close()
            raise ValueError(f"Rentang halaman '{page_range_str}' tidak valid atau melebihi total {total_original_pages} halaman.")

        extracted_doc = fitz.open()
        for p_num in selected_pages:
            # fitz is 0-indexed
            extracted_doc.insert_pdf(doc, from_page=p_num-1, to_page=p_num-1)

        extracted_doc.save(output_path, deflate=True, garbage=3)
        extracted_pages_count = len(extracted_doc)
        extracted_doc.close()
        doc.close()

        return {
            "status": "success",
            "mode": "range",
            "original_pages": total_original_pages,
            "extracted_pages": extracted_pages_count,
            "original_size": original_size,
            "new_size": os.path.getsize(output_path),
            "output_path": output_path,
            "extension": "pdf"
        }

    else:
        # Mode 'all': Extract every single page as a standalone PDF and package into a ZIP archive
        temp_dir = os.path.join(output_dir, f"temp_split_{os.getpid()}")
        os.makedirs(temp_dir, exist_ok=True)
        temp_pdf_files = []

        try:
            with zipfile.ZipFile(output_path, 'w', zipfile.ZIP_DEFLATED) as zip_out:
                for idx in range(1, total_original_pages + 1):
                    single_doc = fitz.open()
                    single_doc.insert_pdf(doc, from_page=idx-1, to_page=idx-1)
                    
                    page_filename = f"{base_name}_halaman_{idx}.pdf"
                    temp_pdf_path = os.path.join(temp_dir, page_filename)
                    single_doc.save(temp_pdf_path, deflate=True, garbage=3)
                    single_doc.close()
                    
                    zip_out.write(temp_pdf_path, page_filename)
                    temp_pdf_files.append(temp_pdf_path)

            doc.close()
        finally:
            for tf in temp_pdf_files:
                if os.path.exists(tf):
                    try:
                        os.remove(tf)
                    except Exception:
                        pass
            if os.path.exists(temp_dir):
                try:
                    os.rmdir(temp_dir)
                except Exception:
                    pass

        return {
            "status": "success",
            "mode": "all",
            "original_pages": total_original_pages,
            "extracted_pages": total_original_pages,
            "original_size": original_size,
            "new_size": os.path.getsize(output_path),
            "output_path": output_path,
            "extension": "zip"
        }

def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python split_pdf.py <input_pdf> <output_path> [mode: all|range] [page_range_str]"
        }))
        sys.exit(1)

    input_pdf = os.path.abspath(sys.argv[1])
    output_path = os.path.abspath(sys.argv[2])
    mode = sys.argv[3].lower() if len(sys.argv) > 3 else "all"
    page_range = sys.argv[4] if len(sys.argv) > 4 else ""

    # Special command: count pages
    if mode == "--count":
        try:
            cnt = count_pages(input_pdf)
            print(json.dumps({"status": "success", "page_count": cnt}))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    try:
        res = split_pdf(input_pdf, output_path, mode, page_range)
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
