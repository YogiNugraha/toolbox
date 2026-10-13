#!/usr/bin/env python3
"""
Watermark PDF Document - High-Performance Text & Image Watermarking Engine.
Powered by PyMuPDF (fitz) and Pillow for crisp, vector-lossless document stamping.
"""

import sys
import os
import io
import json
import re
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

def apply_text_watermark(page, cfg, overlay=True):
    import fitz
    text = cfg.get("text", "CONFIDENTIAL")
    if not text:
        return

    fontsize = float(cfg.get("fontsize", 48))
    color = tuple(float(c) for c in cfg.get("color", [0.5, 0.5, 0.5]))
    opacity = float(cfg.get("opacity", 0.25))
    rotation = int(cfg.get("rotation", 45))
    position = cfg.get("position", "center") # center, top, bottom, tiled

    page_rect = page.rect
    pw, ph = page_rect.width, page_rect.height
    length = fitz.get_text_length(text, fontname="helv", fontsize=fontsize)

    def draw_at(cx, cy, angle, fsize=fontsize, opac=opacity):
        t_len = fitz.get_text_length(text, fontname="helv", fontsize=fsize)
        start_pt = fitz.Point(cx - t_len / 2, cy + fsize / 3)
        morph = (fitz.Point(cx, cy), fitz.Matrix(angle)) if angle != 0 else None
        page.insert_text(
            start_pt,
            text,
            fontsize=fsize,
            fontname="helv",
            color=color,
            fill_opacity=opac,
            morph=morph,
            overlay=overlay
        )

    if position == "center":
        draw_at(pw / 2, ph / 2, rotation)
    elif position == "top":
        draw_at(pw / 2, ph * 0.15, rotation)
    elif position == "bottom":
        draw_at(pw / 2, ph * 0.85, rotation)
    elif position == "tiled":
        # 3x3 repetition grid with subtle opacity
        grid_x = [pw * 0.2, pw * 0.5, pw * 0.8]
        grid_y = [ph * 0.2, ph * 0.5, ph * 0.8]
        tiled_fsize = max(20.0, fontsize * 0.7)
        for gx in grid_x:
            for gy in grid_y:
                draw_at(gx, gy, rotation, fsize=tiled_fsize, opac=opacity * 0.85)

def apply_image_watermark(page, cfg, overlay=True):
    import fitz
    from PIL import Image
    image_path = cfg.get("image_path")
    if not image_path or not os.path.exists(image_path):
        return

    opacity = float(cfg.get("opacity", 0.3))
    scale = float(cfg.get("scale", 0.4)) # percentage of page width
    position = cfg.get("position", "center") # center, top-left, top-right, bottom-left, bottom-right

    # Process image opacity via Pillow
    im = Image.open(image_path).convert("RGBA")
    r, g, b, a = im.split()
    a = a.point(lambda p: int(p * opacity))
    im.putalpha(a)

    buf = io.BytesIO()
    im.save(buf, format="PNG")
    img_bytes = buf.getvalue()

    orig_w, orig_h = im.size
    aspect = orig_h / max(1, orig_w)

    page_rect = page.rect
    target_w = page_rect.width * scale
    target_h = target_w * aspect

    pw, ph = page_rect.width, page_rect.height
    margin = 36.0 # 0.5 inch

    if position == "center":
        x0 = (pw - target_w) / 2
        y0 = (ph - target_h) / 2
    elif position == "top-left":
        x0 = margin
        y0 = margin
    elif position == "top-right":
        x0 = pw - target_w - margin
        y0 = margin
    elif position == "bottom-left":
        x0 = margin
        y0 = ph - target_h - margin
    elif position == "bottom-right":
        x0 = pw - target_w - margin
        y0 = ph - target_h - margin
    else:
        x0 = (pw - target_w) / 2
        y0 = (ph - target_h) / 2

    rect = fitz.Rect(x0, y0, x0 + target_w, y0 + target_h)
    page.insert_image(rect, stream=img_bytes, keep_proportion=True, overlay=overlay)

def watermark_pdf(input_path, output_path, config):
    import fitz
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_path}")

    original_size = os.path.getsize(input_path)
    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    wm_type = config.get("type", "text")
    page_mode = config.get("page_mode", "all") # all, first, range
    page_range = config.get("page_range", "")
    layer = config.get("layer", "over") # over, under
    overlay = (layer == "over")

    with fitz.open(input_path) as doc:
        if doc.is_encrypted and doc.needs_pass:
            raise ValueError("File PDF ini terproteksi password. Silakan buka proteksinya terlebih dahulu melalui tool Unlock PDF.")

        total_pages = len(doc)
        if total_pages == 0:
            raise ValueError("Dokumen PDF tidak memiliki halaman.")

        # Determine target pages
        if page_mode == "first":
            target_indices = [0]
        elif page_mode == "range" and page_range:
            page_numbers = parse_page_range(page_range, total_pages)
            target_indices = [p - 1 for p in page_numbers if 0 <= p - 1 < total_pages]
            if not target_indices:
                target_indices = list(range(total_pages))
        else:
            target_indices = list(range(total_pages))

        # Apply watermark to target pages
        for idx in target_indices:
            page = doc[idx]
            if wm_type == "image":
                apply_image_watermark(page, config, overlay=overlay)
            else:
                apply_text_watermark(page, config, overlay=overlay)

        doc.save(output_path, deflate=True)

    new_size = os.path.getsize(output_path)
    return {
        "status": "success",
        "original_size": original_size,
        "new_size": new_size,
        "total_pages": total_pages,
        "watermarked_pages": len(target_indices),
        "output_path": output_path
    }

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Argumen tidak lengkap."}))
        sys.exit(1)

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

    if len(sys.argv) < 4:
        print(json.dumps({"status": "error", "message": "Gunakan: watermark_pdf.py <input_pdf> <output_pdf> <config_json>"}))
        sys.exit(1)

    input_path = os.path.abspath(sys.argv[1])
    output_path = os.path.abspath(sys.argv[2])
    try:
        config = json.loads(sys.argv[3])
    except Exception as e:
        print(json.dumps({"status": "error", "message": f"Config JSON tidak valid: {str(e)}"}))
        sys.exit(1)

    try:
        res = watermark_pdf(input_path, output_path, config)
        print(json.dumps(res))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
