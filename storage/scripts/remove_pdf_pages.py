#!/usr/bin/env python3
"""
Remove PDF Pages Engine - Fast, Lossless Page Deletion with Visual Thumbnails.
Powered by PyMuPDF (fitz) for instant vector pruning and stream deflating.
"""

import sys
import os
import re
import json
import site

# Dynamically add all potential site-packages directories (supports Laragon/Apache runtime)
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
    Parse a range string like '2, 4-6, 9' into sorted list of unique 1-indexed integers.
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

def inspect_pdf(pdf_path):
    """Inspect basic metadata of PDF."""
    import fitz
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {pdf_path}")

    with fitz.open(pdf_path) as doc:
        return {
            "status": "success",
            "is_encrypted": bool(doc.is_encrypted),
            "needs_pass": bool(doc.needs_pass),
            "total_pages": len(doc),
            "file_size": os.path.getsize(pdf_path)
        }

def generate_thumbnails(pdf_path, output_dir, max_pages=36):
    """Render thumbnail images of PDF pages for visual selection grid."""
    import fitz
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {pdf_path}")

    os.makedirs(output_dir, exist_ok=True)
    thumbnails = []

    with fitz.open(pdf_path) as doc:
        total_pages = len(doc)
        limit = min(total_pages, int(max_pages))
        matrix = fitz.Matrix(0.55, 0.55) # Compact ~150-180px width

        for idx in range(limit):
            page_num = idx + 1
            filename = f"thumb_page_{page_num}.png"
            dest_path = os.path.join(output_dir, filename)
            
            page = doc[idx]
            pix = page.get_pixmap(matrix=matrix, alpha=False)
            pix.save(dest_path)
            
            thumbnails.append({
                "page": page_num,
                "file": filename
            })

    return {
        "status": "success",
        "total_pages": total_pages,
        "rendered_count": len(thumbnails),
        "thumbnails": thumbnails
    }

def remove_pages(input_pdf, output_pdf, pages_to_remove_str):
    """Remove specified pages from PDF and save clean output."""
    import fitz
    if not os.path.exists(input_pdf):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_pdf}")

    original_size = os.path.getsize(input_pdf)
    output_dir = os.path.dirname(os.path.abspath(output_pdf))
    os.makedirs(output_dir, exist_ok=True)

    with fitz.open(input_pdf) as doc:
        total_pages = len(doc)
        if total_pages == 0:
            raise ValueError("Dokumen PDF tidak memiliki halaman.")

        pages_to_remove = parse_page_range(pages_to_remove_str, total_pages)
        if not pages_to_remove:
            raise ValueError("Tidak ada halaman yang dipilih untuk dihapus.")

        kept_pages = [p for p in range(1, total_pages + 1) if p not in pages_to_remove]
        if not kept_pages:
            raise ValueError("Tidak dapat menghapus seluruh halaman. Dokumen PDF harus menyisakan setidaknya 1 halaman.")

        # PyMuPDF doc.select takes 0-indexed list
        doc.select([p - 1 for p in kept_pages])
        doc.save(output_pdf, garbage=4, deflate=True)

    new_size = os.path.getsize(output_pdf)
    return {
        "status": "success",
        "original_size": original_size,
        "new_size": new_size,
        "total_pages_before": total_pages,
        "total_pages_after": len(kept_pages),
        "removed_count": len(pages_to_remove),
        "removed_pages": pages_to_remove,
        "output_path": output_pdf
    }

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Argumen tidak lengkap."}))
        sys.exit(1)

    # 1. Inspect
    if sys.argv[1] == "--inspect":
        if len(sys.argv) < 3:
            print(json.dumps({"status": "error", "message": "Path PDF diperlukan."}))
            sys.exit(1)
        try:
            res = inspect_pdf(os.path.abspath(sys.argv[2]))
            print(json.dumps(res))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    # 2. Thumbnails
    if sys.argv[1] == "--thumbnails":
        if len(sys.argv) < 4:
            print(json.dumps({"status": "error", "message": "Gunakan: --thumbnails <input_pdf> <output_dir> [max_pages]"}))
            sys.exit(1)
        try:
            input_pdf = os.path.abspath(sys.argv[2])
            output_dir = os.path.abspath(sys.argv[3])
            max_pages = int(sys.argv[4]) if len(sys.argv) > 4 else 36
            res = generate_thumbnails(input_pdf, output_dir, max_pages)
            print(json.dumps(res))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    # 3. Remove Pages
    if len(sys.argv) < 4:
        print(json.dumps({"status": "error", "message": "Gunakan: remove_pdf_pages.py <input_pdf> <output_pdf> <pages_to_remove_str>"}))
        sys.exit(1)

    input_pdf = os.path.abspath(sys.argv[1])
    output_pdf = os.path.abspath(sys.argv[2])
    pages_to_remove_str = sys.argv[3]

    try:
        res = remove_pages(input_pdf, output_pdf, pages_to_remove_str)
        print(json.dumps(res))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
