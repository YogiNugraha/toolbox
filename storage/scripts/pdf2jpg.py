#!/usr/bin/env python3
"""
Convert PDF Document Pages into High Resolution JPG Images.
Supports single-page direct JPG output or multi-page ZIP packaging.
"""

import sys
import os
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

def count_pages(pdf_path):
    import fitz
    with fitz.open(pdf_path) as doc:
        return len(doc)

def convert_pdf_to_jpg(input_pdf, output_path, quality="standard"):
    if not os.path.exists(input_pdf):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_pdf}")

    import fitz
    doc = fitz.open(input_pdf)
    total_pages = len(doc)
    original_size = os.path.getsize(input_pdf)

    if total_pages == 0:
        doc.close()
        raise ValueError("Dokumen PDF tidak memiliki halaman.")

    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    base_name = os.path.splitext(os.path.basename(input_pdf))[0]

    # Matrix scale: standard = 2.0x (144-150 DPI), hd = 4.0x (300 DPI)
    scale = 4.0 if quality.lower() == "hd" else 2.0
    mat = fitz.Matrix(scale, scale)

    if total_pages == 1:
        # Single page -> direct JPG output
        page = doc[0]
        pix = page.get_pixmap(matrix=mat, alpha=False)
        pix.save(output_path, "jpeg")
        doc.close()

        if not os.path.exists(output_path) or os.path.getsize(output_path) == 0:
            raise RuntimeError("Gagal menghasilkan gambar JPG.")

        return {
            "status": "success",
            "page_count": 1,
            "quality": quality,
            "format": "jpg",
            "width": pix.width,
            "height": pix.height,
            "original_size": original_size,
            "new_size": os.path.getsize(output_path),
            "output_path": output_path,
            "extension": "jpg"
        }
    else:
        # Multi-page -> Render each page and bundle into ZIP
        temp_dir = os.path.join(output_dir, f"temp_jpg_{os.getpid()}")
        os.makedirs(temp_dir, exist_ok=True)
        temp_jpg_files = []

        try:
            with zipfile.ZipFile(output_path, 'w', zipfile.ZIP_DEFLATED) as zip_out:
                for idx, page in enumerate(doc, 1):
                    pix = page.get_pixmap(matrix=mat, alpha=False)
                    jpg_filename = f"{base_name}_halaman_{idx}.jpg"
                    temp_jpg_path = os.path.join(temp_dir, jpg_filename)
                    pix.save(temp_jpg_path, "jpeg")
                    
                    zip_out.write(temp_jpg_path, jpg_filename)
                    temp_jpg_files.append(temp_jpg_path)

            doc.close()
        finally:
            for tf in temp_jpg_files:
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

        if not os.path.exists(output_path) or os.path.getsize(output_path) == 0:
            raise RuntimeError("Gagal menghasilkan file arsip ZIP gambar JPG.")

        return {
            "status": "success",
            "page_count": total_pages,
            "quality": quality,
            "format": "zip",
            "original_size": original_size,
            "new_size": os.path.getsize(output_path),
            "output_path": output_path,
            "extension": "zip"
        }

def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python pdf2jpg.py <input_pdf> <output_path> [quality: standard|hd]"
        }))
        sys.exit(1)

    input_pdf = os.path.abspath(sys.argv[1])
    output_path = os.path.abspath(sys.argv[2])
    quality = sys.argv[3].lower() if len(sys.argv) > 3 else "standard"

    # Special command: count pages
    if quality == "--count":
        try:
            cnt = count_pages(input_pdf)
            print(json.dumps({"status": "success", "page_count": cnt}))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    try:
        res = convert_pdf_to_jpg(input_pdf, output_path, quality)
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
