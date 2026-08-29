#!/usr/bin/env python3
"""
Rotate PDF Document - Lossless vector rotation engine using PyMuPDF (fitz).
Supports individual page rotation and batch document rotation.
"""

import sys
import os
import json
import base64
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
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {pdf_path}")
    with fitz.open(pdf_path) as doc:
        return len(doc)

def render_thumbnails(pdf_path, dpi=72):
    """
    Renders lightweight base64 thumbnail images for all pages in PDF.
    """
    import fitz
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {pdf_path}")

    doc = fitz.open(pdf_path)
    total_pages = len(doc)
    thumbnails = []

    for idx, page in enumerate(doc, 1):
        pix = page.get_pixmap(dpi=dpi)
        png_bytes = pix.tobytes("png")
        b64_str = "data:image/png;base64," + base64.b64encode(png_bytes).decode("ascii")
        
        thumbnails.append({
            "page": idx,
            "width": pix.width,
            "height": pix.height,
            "current_rotation": page.rotation,
            "image": b64_str
        })

    doc.close()
    return {
        "status": "success",
        "total_pages": total_pages,
        "thumbnails": thumbnails
    }

def rotate_pdf(input_pdf, output_path, rotations_map=None, global_rotation=0):
    """
    Applies lossless page rotations to a PDF.
    rotations_map: dict of { "page_number": rotation_angle_deg }
    global_rotation: default rotation angle in deg (0, 90, 180, 270)
    """
    import fitz
    if not os.path.exists(input_pdf):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_pdf}")

    doc = fitz.open(input_pdf)
    total_pages = len(doc)
    original_size = os.path.getsize(input_pdf)

    if total_pages == 0:
        doc.close()
        raise ValueError("Dokumen PDF tidak memiliki halaman.")

    if rotations_map is None:
        rotations_map = {}

    rotated_count = 0

    for idx, page in enumerate(doc, 1):
        page_str = str(idx)
        # Check if individual rotation is specified, otherwise fallback to global_rotation
        add_angle = int(rotations_map.get(page_str, global_rotation))
        
        if add_angle % 360 != 0:
            current_rot = page.rotation
            new_rot = (current_rot + add_angle) % 360
            page.set_rotation(new_rot)
            rotated_count += 1

    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    # Lossless vector save with garbage collection & stream deflation
    doc.save(output_path, deflate=True, garbage=3)
    doc.close()

    new_size = os.path.getsize(output_path)

    return {
        "status": "success",
        "total_pages": total_pages,
        "rotated_pages": rotated_count,
        "original_size": original_size,
        "new_size": new_size,
        "output_path": output_path,
        "extension": "pdf"
    }

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python rotate_pdf.py <input_pdf> <output_path|--count|--render-thumbnails> [rotations_json] [global_angle]"
        }))
        sys.exit(1)

    input_pdf = os.path.abspath(sys.argv[1])
    action = sys.argv[2]

    # Action 1: Count Pages
    if action == "--count":
        try:
            cnt = count_pages(input_pdf)
            print(json.dumps({"status": "success", "page_count": cnt}))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    # Action 2: Render Thumbnails
    if action == "--render-thumbnails":
        dpi = int(sys.argv[3]) if len(sys.argv) > 3 else 72
        try:
            res = render_thumbnails(input_pdf, dpi)
            print(json.dumps(res))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    # Action 3: Rotate PDF: python rotate_pdf.py <input_pdf> <output_path> [rotations_json] [global_angle]
    output_path = os.path.abspath(action)
    rotations_json = sys.argv[3] if len(sys.argv) > 3 else "{}"
    global_rot = 0
    if len(sys.argv) > 4:
        try:
            global_rot = int(sys.argv[4])
        except ValueError:
            global_rot = 0

    rot_map = {}
    if rotations_json:
        if os.path.exists(rotations_json):
            try:
                with open(rotations_json, "r", encoding="utf-8") as f:
                    rot_map = json.load(f)
            except Exception:
                rot_map = {}
        else:
            try:
                rot_map = json.loads(rotations_json)
            except Exception:
                # Try cleaning escaped quotes
                try:
                    clean_str = rotations_json.replace('\\"', '"').replace("\\'", "'")
                    rot_map = json.loads(clean_str)
                except Exception:
                    rot_map = {}

    try:
        res = rotate_pdf(input_pdf, output_path, rot_map, global_rot)
        print(json.dumps(res))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)
