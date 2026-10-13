#!/usr/bin/env python3
"""
Page Number PDF Engine - Fast, Vector-Lossless PDF Pagination.
Powered by PyMuPDF (fitz) with flexible positions, roman/arabic numerals, and live preview rendering.
"""

import sys
import os
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

def to_roman(num):
    """Convert integer to Roman numeral."""
    if num <= 0:
        return str(num)
    val = [1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1]
    syb = ["M", "CM", "D", "CD", "C", "XC", "L", "XL", "X", "IX", "V", "IV", "I"]
    roman_num = ""
    i = 0
    while num > 0:
        for _ in range(num // val[i]):
            roman_num += syb[i]
            num -= val[i]
        i += 1
    return roman_num

def parse_hex_color(hex_str, default=(0.2, 0.25, 0.33)):
    """Convert hex color (#334155) to float RGB tuple (0.0 to 1.0)."""
    if not hex_str:
        return default
    hex_clean = str(hex_str).lstrip('#').strip()
    if len(hex_clean) == 6:
        try:
            r = int(hex_clean[0:2], 16) / 255.0
            g = int(hex_clean[2:4], 16) / 255.0
            b = int(hex_clean[4:6], 16) / 255.0
            return (r, g, b)
        except ValueError:
            pass
    return default

def format_number_text(fmt, num, total_num):
    """Format pagination string."""
    if fmt == 'n':
        return str(num)
    elif fmt == 'page_n':
        return f"Halaman {num}"
    elif fmt == 'page_n_of_total':
        return f"Halaman {num} dari {total_num}"
    elif fmt == 'n_slash_total':
        return f"{num} / {total_num}"
    elif fmt == 'roman_lower':
        return to_roman(num).lower()
    elif fmt == 'roman_upper':
        return to_roman(num).upper()
    return str(num)

def add_number_to_page(page, text, position, font_size, font_color, margin_x, margin_y):
    """Stamp number onto single fitz.Page using textbox."""
    import fitz
    rect = page.rect
    w, h = rect.width, rect.height

    # Define alignment and text bounds
    # Alignments: 0=Left, 1=Center, 2=Right
    box_height = font_size + 14

    if position == 'bottom-center':
        box = fitz.Rect(margin_x, h - margin_y - box_height, w - margin_x, h - margin_y)
        align = fitz.TEXT_ALIGN_CENTER
    elif position == 'bottom-right':
        box = fitz.Rect(margin_x, h - margin_y - box_height, w - margin_x, h - margin_y)
        align = fitz.TEXT_ALIGN_RIGHT
    elif position == 'bottom-left':
        box = fitz.Rect(margin_x, h - margin_y - box_height, w - margin_x, h - margin_y)
        align = fitz.TEXT_ALIGN_LEFT
    elif position == 'top-center':
        box = fitz.Rect(margin_x, margin_y, w - margin_x, margin_y + box_height)
        align = fitz.TEXT_ALIGN_CENTER
    elif position == 'top-right':
        box = fitz.Rect(margin_x, margin_y, w - margin_x, margin_y + box_height)
        align = fitz.TEXT_ALIGN_RIGHT
    elif position == 'top-left':
        box = fitz.Rect(margin_x, margin_y, w - margin_x, margin_y + box_height)
        align = fitz.TEXT_ALIGN_LEFT
    else:
        # Default to bottom-center
        box = fitz.Rect(margin_x, h - margin_y - box_height, w - margin_x, h - margin_y)
        align = fitz.TEXT_ALIGN_CENTER

    page.insert_textbox(
        box,
        text,
        fontsize=font_size,
        fontname="helv",
        color=font_color,
        align=align
    )

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

def render_preview(input_path, output_png, options, page_idx=1):
    """Render a single page with numbering applied as PNG thumbnail."""
    import fitz
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_path}")

    position = options.get('position', 'bottom-center')
    fmt = options.get('format', 'page_n_of_total')
    font_size = float(options.get('font_size', 10))
    font_color = parse_hex_color(options.get('font_color', '#334155'))
    start_page = int(options.get('start_page', 1))
    first_number = int(options.get('first_number', 1))
    margin_x = float(options.get('margin_x', 36))
    margin_y = float(options.get('margin_y', 36))

    with fitz.open(input_path) as doc:
        total_pages = len(doc)
        if total_pages == 0:
            raise ValueError("Dokumen PDF tidak memiliki halaman.")

        # Ensure page index is valid (1-indexed)
        target_page = max(1, min(page_idx, total_pages))
        page = doc[target_page - 1]

        # Only apply number if target page is >= start_page
        if target_page >= start_page:
            current_num = (target_page - start_page) + first_number
            total_numbered = (total_pages - start_page) + first_number
            text = format_number_text(fmt, current_num, total_numbered)
            add_number_to_page(page, text, position, font_size, font_color, margin_x, margin_y)

        # Render pixmap (scale ~1.5 for crisp thumbnail preview)
        matrix = fitz.Matrix(1.5, 1.5)
        pix = page.get_pixmap(matrix=matrix, alpha=False)

        output_dir = os.path.dirname(os.path.abspath(output_png))
        os.makedirs(output_dir, exist_ok=True)
        pix.save(output_png)

    return {
        "status": "success",
        "output_png": output_png,
        "page_rendered": target_page,
        "total_pages": total_pages
    }

def paginate_pdf(input_path, output_path, options):
    """Add page numbers to PDF according to options."""
    import fitz
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_path}")

    position = options.get('position', 'bottom-center')
    fmt = options.get('format', 'page_n_of_total')
    font_size = float(options.get('font_size', 10))
    font_color = parse_hex_color(options.get('font_color', '#334155'))
    start_page = int(options.get('start_page', 1))
    first_number = int(options.get('first_number', 1))
    margin_x = float(options.get('margin_x', 36))
    margin_y = float(options.get('margin_y', 36))

    original_size = os.path.getsize(input_path)
    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    with fitz.open(input_path) as doc:
        total_pages = len(doc)
        if total_pages == 0:
            raise ValueError("Dokumen PDF tidak memiliki halaman.")

        total_numbered = (total_pages - start_page) + first_number
        pages_processed = 0

        for idx in range(total_pages):
            page_num = idx + 1
            if page_num >= start_page:
                current_num = (page_num - start_page) + first_number
                text = format_number_text(fmt, current_num, total_numbered)
                add_number_to_page(doc[idx], text, position, font_size, font_color, margin_x, margin_y)
                pages_processed += 1

        doc.save(output_path, deflate=True)

    new_size = os.path.getsize(output_path)
    return {
        "status": "success",
        "original_size": original_size,
        "new_size": new_size,
        "total_pages": total_pages,
        "pages_processed": pages_processed,
        "output_path": output_path
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

    # 2. Preview
    if sys.argv[1] == "--preview":
        if len(sys.argv) < 5:
            print(json.dumps({"status": "error", "message": "Gunakan: --preview <input_pdf> <output_png> '<options_json>' [page_num]"}))
            sys.exit(1)
        try:
            input_path = os.path.abspath(sys.argv[2])
            output_png = os.path.abspath(sys.argv[3])
            options = json.loads(sys.argv[4])
            page_idx = int(sys.argv[5]) if len(sys.argv) > 5 else 1
            res = render_preview(input_path, output_png, options, page_idx)
            print(json.dumps(res))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    # 3. Paginate
    if len(sys.argv) < 4:
        print(json.dumps({"status": "error", "message": "Gunakan: page_number_pdf.py <input_pdf> <output_pdf> '<options_json>'"}))
        sys.exit(1)

    input_path = os.path.abspath(sys.argv[1])
    output_path = os.path.abspath(sys.argv[2])
    try:
        options = json.loads(sys.argv[3])
    except Exception:
        options = {}

    try:
        res = paginate_pdf(input_path, output_path, options)
        print(json.dumps(res))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
