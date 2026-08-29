#!/usr/bin/env python3
"""
Convert One or Multiple JPG/PNG/WebP Images into a Unified High-Quality PDF Document.
Supports Page Layout (A4 Auto / Portrait / Landscape / Fit) and Margins.
"""

import sys
import os
import site

# 1. Dynamically ensure User Site Packages are loaded even if Apache/PHP runs without APPDATA
user_site = site.getusersitepackages()
if user_site and os.path.exists(user_site) and user_site not in sys.path:
    sys.path.insert(0, user_site)

for user_base in [os.environ.get('USERPROFILE', ''), r'C:\Users\Yogi Nugraha', r'C:\Users\Default']:
    if user_base:
        for py_ver in ['Python313', 'Python312', 'Python311', 'Python310']:
            cand = os.path.join(user_base, 'AppData', 'Roaming', 'Python', py_ver, 'site-packages')
            if os.path.exists(cand) and cand not in sys.path:
                sys.path.insert(0, cand)

import json
import fitz  # PyMuPDF

A4_WIDTH = 595.28
A4_HEIGHT = 841.89

def get_image_size(img_path):
    """
    Get image dimensions safely using PyMuPDF or PIL.
    """
    try:
        # PyMuPDF direct pixmap dimensions
        with fitz.open(img_path) as doc:
            page = doc[0]
            return float(page.rect.width), float(page.rect.height)
    except Exception:
        try:
            from PIL import Image
            with Image.open(img_path) as pil_img:
                return float(pil_img.width), float(pil_img.height)
        except Exception as e:
            raise RuntimeError(f"Gagal membaca ukuran gambar {os.path.basename(img_path)}: {str(e)}")

def convert_images_to_pdf(input_images, output_path, layout="a4_auto", margin="small"):
    if not input_images:
        raise ValueError("Setidaknya diperlukan 1 file gambar untuk dikonversi ke PDF.")

    margin_map = {
        "none": 0.0,
        "small": 20.0,
        "normal": 40.0
    }
    m = margin_map.get(margin.lower(), 20.0)

    total_original_size = 0
    valid_images = []

    for img_path in input_images:
        if not os.path.exists(img_path):
            raise FileNotFoundError(f"File gambar tidak ditemukan: {img_path}")
        total_original_size += os.path.getsize(img_path)
        valid_images.append(img_path)

    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    doc = fitz.open()

    for img_path in valid_images:
        img_w, img_h = get_image_size(img_path)
        is_landscape = (img_w > img_h)

        if layout == "fit":
            # Page matches exact image aspect/dimensions
            page_w = float(img_w)
            page_h = float(img_h)
            
            # Scale down if large to keep standard PDF dimensions
            max_dim = 1440.0
            if max(page_w, page_h) > max_dim:
                scale = max_dim / max(page_w, page_h)
                page_w *= scale
                page_h *= scale

            page = doc.new_page(width=page_w, height=page_h)
            rect = fitz.Rect(0, 0, page_w, page_h)
            page.insert_image(rect, filename=img_path)

        else:
            # A4 Layout
            if layout == "a4_portrait":
                pw, ph = A4_WIDTH, A4_HEIGHT
            elif layout == "a4_landscape":
                pw, ph = A4_HEIGHT, A4_WIDTH
            else:  # a4_auto
                pw, ph = (A4_HEIGHT, A4_WIDTH) if is_landscape else (A4_WIDTH, A4_HEIGHT)

            page = doc.new_page(width=pw, height=ph)

            avail_w = max(10.0, pw - (2 * m))
            avail_h = max(10.0, ph - (2 * m))

            # Calculate scaled image rect preserving aspect ratio
            img_aspect = img_w / img_h
            avail_aspect = avail_w / avail_h

            if img_aspect > avail_aspect:
                # Width is limiting factor
                target_w = avail_w
                target_h = avail_w / img_aspect
            else:
                # Height is limiting factor
                target_h = avail_h
                target_w = avail_h * img_aspect

            # Center inside page
            x0 = (pw - target_w) / 2.0
            y0 = (ph - target_h) / 2.0
            x1 = x0 + target_w
            y1 = y0 + target_h

            rect = fitz.Rect(x0, y0, x1, y1)
            page.insert_image(rect, filename=img_path)

    doc.save(output_path, deflate=True, garbage=3)
    page_count = len(doc)
    doc.close()

    if not os.path.exists(output_path) or os.path.getsize(output_path) == 0:
        raise RuntimeError("Gagal menghasilkan file PDF yang valid.")

    return {
        "status": "success",
        "page_count": page_count,
        "images_count": len(valid_images),
        "layout": layout,
        "margin": margin,
        "original_size": total_original_size,
        "new_size": os.path.getsize(output_path),
        "output_path": output_path,
        "extension": "pdf"
    }

def main():
    if len(sys.argv) < 5:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python jpg2pdf.py <output_path> <layout:a4_auto|a4_portrait|a4_landscape|fit> <margin:none|small|normal> <input_image_1> [input_image_2] ..."
        }))
        sys.exit(1)

    output_path = os.path.abspath(sys.argv[1])
    layout = sys.argv[2].lower()
    margin = sys.argv[3].lower()
    input_images = [os.path.abspath(p) for p in sys.argv[4:]]

    try:
        res = convert_images_to_pdf(input_images, output_path, layout, margin)
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
