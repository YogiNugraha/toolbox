#!/usr/bin/env python3
"""
PDF Compression Script using PyMuPDF
High performance PDF compressor with stream deflation, garbage collection,
font deduplication, and image raster optimization.
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

try:
    import pymupdf as fitz
except ImportError:
    try:
        import fitz
    except ImportError:
        print(json.dumps({
            "status": "error",
            "message": "PyMuPDF library is not installed in Python environment."
        }))
        sys.exit(1)

def compress_pdf(input_path, output_path, dpi=150, quality=75, strip_metadata=False):
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"Input file not found: {input_path}")

    original_size = os.path.getsize(input_path)
    
    # Open PDF document
    doc = fitz.open(input_path)
    page_count = len(doc)

    if strip_metadata:
        doc.set_metadata({})

    # Optimize embedded raster images
    processed_xrefs = set()
    
    for page_idx in range(page_count):
        page = doc[page_idx]
        image_list = page.get_images(full=True)
        
        for img_info in image_list:
            xref = img_info[0]
            if xref in processed_xrefs:
                continue
            processed_xrefs.add(xref)
            
            try:
                base_img = doc.extract_image(xref)
                if not base_img:
                    continue
                    
                image_bytes = base_img.get("image", b"")
                img_width = base_img.get("width", 0)
                img_height = base_img.get("height", 0)
                
                # Check if image is sizeable enough to benefit from recompression
                if len(image_bytes) > 10240 or img_width > 600 or img_height > 600:
                    pix = fitz.Pixmap(doc, xref)
                    
                    # Convert color space if CMYK
                    if pix.n >= 5 or pix.colorspace == fitz.csCMYK:
                        pix = fitz.Pixmap(fitz.csRGB, pix)
                    elif pix.colorspace not in (fitz.csRGB, fitz.csGRAY):
                        pix = fitz.Pixmap(fitz.csRGB, pix)

                    jpeg_data = pix.tobytes(output="jpg", jpg_quality=int(quality))
                    
                    # Only replace if compressed version is smaller
                    if len(jpeg_data) < len(image_bytes):
                        doc.update_stream(xref, jpeg_data)
            except Exception:
                # If single image optimization encounters error, continue safely
                continue

    # Ensure output directory exists
    os.makedirs(os.path.dirname(os.path.abspath(output_path)), exist_ok=True)

    # Save with maximal compression parameters
    # garbage=4: remove unused objects, compact streams
    # deflate=True: compress uncompressed streams
    # clean=True: sanitize content streams
    # deflate_images=True, deflate_fonts=True: compress font & image data
    doc.save(
        output_path,
        garbage=4,
        deflate=True,
        clean=True,
        deflate_images=True,
        deflate_fonts=True
    )
    doc.close()

    if not os.path.exists(output_path):
        raise RuntimeError(f"Output file was not created: {output_path}")

    compressed_size = os.path.getsize(output_path)
    savings_bytes = original_size - compressed_size
    savings_percentage = round((savings_bytes / original_size) * 100, 2) if original_size > 0 else 0

    return {
        "status": "success",
        "original_size": original_size,
        "compressed_size": compressed_size,
        "savings_bytes": savings_bytes,
        "savings_percentage": max(0.0, savings_percentage),
        "page_count": page_count
    }

def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: compress_pdf.py <input_path> <output_path> [dpi] [quality] [strip_metadata]"
        }))
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]
    
    dpi = int(sys.argv[3]) if len(sys.argv) > 3 else 150
    quality = int(sys.argv[4]) if len(sys.argv) > 4 else 75
    strip_metadata = bool(int(sys.argv[5])) if len(sys.argv) > 5 else False

    try:
        result = compress_pdf(input_path, output_path, dpi, quality, strip_metadata)
        print(json.dumps(result))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))
        sys.exit(1)

if __name__ == "__main__":
    main()
