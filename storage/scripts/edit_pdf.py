#!/usr/bin/env python3
"""
Interactive Visual PDF Studio & Editor Engine:
Renders PDF pages to high-resolution images, composites visual overlays (Text, Whiteout, Draw, Highlighter, Images, Signatures),
and outputs sharp edited PDF documents.
"""

import sys
import os
import site
import base64
import json

# 1. Dynamically ensure User Site Packages are loaded
user_site = site.getusersitepackages()
if user_site and os.path.exists(user_site) and user_site not in sys.path:
    sys.path.insert(0, user_site)

for user_base in [os.environ.get('USERPROFILE', ''), r'C:\Users\Yogi Nugraha', r'C:\Users\Default']:
    if user_base:
        for py_ver in ['Python313', 'Python312', 'Python311', 'Python310']:
            cand = os.path.join(user_base, 'AppData', 'Roaming', 'Python', py_ver, 'site-packages')
            if os.path.exists(cand) and cand not in sys.path:
                sys.path.insert(0, cand)

import fitz  # PyMuPDF

def count_pages(pdf_path):
    with fitz.open(pdf_path) as doc:
        return len(doc)

def render_page_to_image(pdf_path, page_num=1, dpi=130):
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {pdf_path}")

    doc = fitz.open(pdf_path)
    total_pages = len(doc)
    
    if page_num < 1 or page_num > total_pages:
        doc.close()
        raise ValueError(f"Nomor halaman {page_num} tidak valid. Total halaman: {total_pages}")

    page = doc[page_num - 1]
    pix = page.get_pixmap(dpi=dpi)
    png_bytes = pix.tobytes("png")
    b64_str = "data:image/png;base64," + base64.b64encode(png_bytes).decode("ascii")
    
    w = pix.width
    h = pix.height
    doc.close()

    return {
        "status": "success",
        "page": page_num,
        "total_pages": total_pages,
        "width": w,
        "height": h,
        "image": b64_str
    }

def render_all_pages_to_dir(pdf_path, output_dir, dpi=130):
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {pdf_path}")

    os.makedirs(output_dir, exist_ok=True)
    doc = fitz.open(pdf_path)
    total_pages = len(doc)
    pages_meta = []

    for idx, page in enumerate(doc, 1):
        pix = page.get_pixmap(dpi=dpi)
        filename = f"page_{idx}.png"
        filepath = os.path.join(output_dir, filename)
        pix.save(filepath)
        pages_meta.append({
            "page": idx,
            "filename": filename,
            "width": pix.width,
            "height": pix.height
        })

    doc.close()
    return {
        "status": "success",
        "total_pages": total_pages,
        "pages": pages_meta
    }

def edit_pdf(input_pdf, output_path, options):
    if not os.path.exists(input_pdf):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_pdf}")

    src_doc = fitz.open(input_pdf)
    total_original_pages = len(src_doc)
    original_size = os.path.getsize(input_pdf)

    if total_original_pages == 0:
        src_doc.close()
        raise ValueError("Dokumen PDF tidak memiliki halaman.")

    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    # 1. Page reordering & deletion
    target_pages = options.get('reorder') or [i + 1 for i in range(total_original_pages)]
    deleted = set(options.get('deleted_pages') or [])

    final_page_indices = []
    for p_num in target_pages:
        try:
            p_val = int(p_num)
            if p_val not in deleted and 1 <= p_val <= total_original_pages:
                final_page_indices.append(p_val)
        except (ValueError, TypeError):
            continue

    if not final_page_indices:
        src_doc.close()
        raise ValueError("Semua halaman telah dihapus. Minimal sisakan 1 halaman dokumen.")

    doc = fitz.open()
    for p_num in final_page_indices:
        doc.insert_pdf(src_doc, from_page=p_num - 1, to_page=p_num - 1)

    src_doc.close()
    final_pages_count = len(doc)

    # 2. Page Overlays (Visual Canvas modifications: Text, Whiteout, Draw, Images, Signatures)
    page_overlays = options.get('page_overlays', {}) or {}
    for idx, p_num in enumerate(final_page_indices):
        orig_str_key = str(p_num)
        curr_str_key = str(idx + 1)
        
        overlay_data = page_overlays.get(orig_str_key) or page_overlays.get(curr_str_key)
        if overlay_data:
            try:
                if overlay_data.startswith('data:image'):
                    overlay_b64 = overlay_data.split(',', 1)[1]
                    raw_png = base64.b64decode(overlay_b64)
                elif os.path.exists(overlay_data):
                    with open(overlay_data, 'rb') as f:
                        raw_png = f.read()
                else:
                    raw_png = base64.b64decode(overlay_data)

                if raw_png and len(raw_png) > 100:
                    page = doc[idx]
                    page.insert_image(page.rect, stream=raw_png)
            except Exception as ex:
                print(f"Warning: Failed to insert overlay for page {idx+1}: {str(ex)}", file=sys.stderr)

    # 3. Rotations
    rotate_all = int(options.get('rotate_all', 0) or 0)
    page_rotations = options.get('page_rotations', {}) or {}

    for idx, page in enumerate(doc, 1):
        rot = int(page_rotations.get(str(idx), rotate_all) or 0)
        if rot:
            page.set_rotation((page.rotation + rot) % 360)

    # 4. Metadata
    metadata_options = options.get('metadata', {}) or {}
    if metadata_options:
        meta = doc.metadata or {}
        for k in ['title', 'author', 'subject', 'keywords']:
            if metadata_options.get(k):
                meta[k] = str(metadata_options[k])
        doc.set_metadata(meta)

    doc.save(output_path, deflate=True, garbage=3)
    doc.close()

    if not os.path.exists(output_path) or os.path.getsize(output_path) == 0:
        raise RuntimeError("Gagal menghasilkan file PDF yang telah diedit.")

    return {
        "status": "success",
        "original_pages": total_original_pages,
        "final_pages": final_pages_count,
        "original_size": original_size,
        "new_size": os.path.getsize(output_path),
        "output_path": output_path,
        "extension": "pdf"
    }

def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python edit_pdf.py <input_pdf> <output_pdf|--count|--render-page|--render-all> [args...]"
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

    # Action 2: Render single page to base64 image: python edit_pdf.py <input.pdf> --render-page <page_num> [dpi]
    if action == "--render-page":
        p_num = int(sys.argv[3]) if len(sys.argv) > 3 else 1
        dpi = int(sys.argv[4]) if len(sys.argv) > 4 else 130
        try:
            res = render_page_to_image(input_pdf, p_num, dpi)
            print(json.dumps(res))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    # Action 3: Render all pages to directory: python edit_pdf.py <input.pdf> --render-all <output_dir> [dpi]
    if action == "--render-all":
        out_dir = os.path.abspath(sys.argv[3]) if len(sys.argv) > 3 else os.path.dirname(input_pdf)
        dpi = int(sys.argv[4]) if len(sys.argv) > 4 else 130
        try:
            res = render_all_pages_to_dir(input_pdf, out_dir, dpi)
            print(json.dumps(res))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    # Action 4: Edit PDF
    output_path = os.path.abspath(sys.argv[2])
    options_arg = sys.argv[3] if len(sys.argv) > 3 else "{}"

    options = {}
    if os.path.exists(options_arg) and options_arg.endswith('.json'):
        try:
            with open(options_arg, 'r', encoding='utf-8') as f:
                options = json.load(f)
        except Exception:
            options = {}
    else:
        try:
            options = json.loads(options_arg) if options_arg else {}
        except Exception:
            options = {}

    try:
        res = edit_pdf(input_pdf, output_path, options)
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
