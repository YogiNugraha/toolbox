#!/usr/bin/env python3
"""
Fully Editable PDF to PowerPoint (.pptx) Conversion Script
Converts PDF documents into real, editable Microsoft PowerPoint presentation slides
with native text boxes, fonts, vectors, and embedded images.
"""

import sys
import os
import shutil
import subprocess
import tempfile
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
        fitz = None

def find_libreoffice():
    """Find LibreOffice console or GUI binary for Windows / Linux."""
    potential_paths = [
        r"C:\Program Files\LibreOffice\program\soffice.com",
        r"C:\Program Files (x86)\LibreOffice\program\soffice.com",
        r"C:\Program Files\LibreOffice\program\soffice.exe",
        r"C:\Program Files (x86)\LibreOffice\program\soffice.exe",
        shutil.which("soffice.com"),
        shutil.which("soffice.exe"),
        shutil.which("soffice"),
        shutil.which("libreoffice"),
    ]
    for path in potential_paths:
        if path and os.path.exists(path):
            return path
    return None

def convert_with_libreoffice(input_path, output_path, soffice_bin):
    """Converts PDF to editable PPTX using LibreOffice Impress engine."""
    with tempfile.TemporaryDirectory() as temp_dir:
        profile_dir = os.path.join(temp_dir, "libreoffice_profile")
        os.makedirs(profile_dir, exist_ok=True)
        profile_uri = "file:///" + profile_dir.replace("\\", "/")

        temp_input = os.path.join(temp_dir, os.path.basename(input_path))
        shutil.copy2(input_path, temp_input)

        cmd = [
            soffice_bin,
            "--headless",
            "--invisible",
            "--nodefault",
            "--nofirststartwizard",
            f"-env:UserInstallation={profile_uri}",
            "--infilter=impress_pdf_import",
            "--convert-to",
            "pptx",
            temp_input,
            "--outdir",
            temp_dir
        ]

        result = subprocess.run(
            cmd,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=180
        )

        expected_filename = os.path.splitext(os.path.basename(temp_input))[0] + ".pptx"
        temp_pptx = os.path.join(temp_dir, expected_filename)

        if not os.path.exists(temp_pptx):
            generated = [f for f in os.listdir(temp_dir) if f.endswith(".pptx")]
            if generated:
                temp_pptx = os.path.join(temp_dir, generated[0])
            else:
                err_msg = result.stderr.strip() or result.stdout.strip() or "Engine failed to convert PDF to PPTX."
                raise RuntimeError(f"LibreOffice conversion failed: {err_msg}")

        output_dir = os.path.dirname(os.path.abspath(output_path))
        os.makedirs(output_dir, exist_ok=True)
        shutil.move(temp_pptx, output_path)

def convert_with_pymupdf_pptx(input_path, output_path):
    """Fallback: converts PDF pages to native PPTX slides with editable text blocks and images."""
    from pptx import Presentation
    from pptx.util import Pt
    import io

    if not fitz:
        raise RuntimeError("PyMuPDF is required for fallback conversion.")

    pdf_doc = fitz.open(input_path)
    slide_count = len(pdf_doc)

    if slide_count == 0:
        raise ValueError("Dokumen PDF kosong.")

    prs = Presentation()
    blank_layout = prs.slide_layouts[6]

    first_page = pdf_doc[0]
    prs.slide_width = Pt(first_page.rect.width)
    prs.slide_height = Pt(first_page.rect.height)

    for page_idx in range(slide_count):
        page = pdf_doc[page_idx]
        slide = prs.slides.add_slide(blank_layout)

        # 1. Render base background graphics/illustrations
        pix = page.get_pixmap(dpi=150)
        img_buffer = io.BytesIO(pix.tobytes("png"))
        slide.shapes.add_picture(img_buffer, 0, 0, width=Pt(page.rect.width), height=Pt(page.rect.height))

        # 2. Extract editable text blocks over the slide
        text_page = page.get_text("dict")
        for block in text_page.get("blocks", []):
            if block.get("type") == 0: # text
                bbox = block.get("bbox", [0, 0, 100, 50])
                bx0, by0, bx1, by1 = bbox
                bw = max(bx1 - bx0, 20)
                bh = max(by1 - by0, 15)

                block_text = ""
                for line in block.get("lines", []):
                    for span in line.get("spans", []):
                        block_text += span.get("text", "") + " "
                    block_text = block_text.rstrip() + "\n"

                block_text = block_text.strip()
                if block_text:
                    tb = slide.shapes.add_textbox(Pt(bx0), Pt(by0), Pt(bw), Pt(bh))
                    tf = tb.text_frame
                    tf.word_wrap = True
                    tf.text = block_text

    prs.save(output_path)
    pdf_doc.close()

def convert_pdf_to_pptx(input_path, output_path):
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"Input file not found: {input_path}")

    original_size = os.path.getsize(input_path)
    soffice_bin = find_libreoffice()

    if soffice_bin:
        try:
            convert_with_libreoffice(input_path, output_path, soffice_bin)
        except Exception:
            # Fallback to PyMuPDF extraction if LibreOffice fails
            convert_with_pymupdf_pptx(input_path, output_path)
    else:
        convert_with_pymupdf_pptx(input_path, output_path)

    new_size = os.path.getsize(output_path)

    # Count slides
    slide_count = 1
    if fitz:
        try:
            doc = fitz.open(input_path)
            slide_count = len(doc)
            doc.close()
        except Exception:
            pass

    return {
        "status": "success",
        "original_size": original_size,
        "new_size": new_size,
        "slide_count": slide_count,
        "output_path": output_path
    }

def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python pdf2pptx.py <input_pdf> <output_pptx>"
        }))
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    try:
        result = convert_pdf_to_pptx(input_path, output_path)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))
        sys.exit(1)

if __name__ == "__main__":
    main()
