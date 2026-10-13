#!/usr/bin/env python3
"""
Unlock PDF Document - Decrypt and permanently remove password protection.
Powered by PyMuPDF (fitz) for fast, lossless vector processing.
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

def check_encryption(pdf_path):
    import fitz
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {pdf_path}")

    with fitz.open(pdf_path) as doc:
        return {
            "status": "success",
            "is_encrypted": bool(doc.is_encrypted),
            "needs_pass": bool(doc.needs_pass),
            "total_pages": len(doc) if not doc.needs_pass else 0,
            "file_size": os.path.getsize(pdf_path)
        }

def unlock_pdf(input_path, output_path, password=""):
    import fitz
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_path}")

    original_size = os.path.getsize(input_path)
    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    with fitz.open(input_path) as doc:
        if doc.is_encrypted:
            # Document is encrypted, verify password
            if not password:
                raise ValueError("Dokumen ini membutuhkan password untuk didekripsi.")

            auth = doc.authenticate(str(password))
            if auth == 0:
                raise ValueError("Password yang Anda masukkan salah. Dokumen tidak dapat didekripsi.")

        total_pages = len(doc)

        # Save with encryption removed completely
        doc.save(
            output_path,
            encryption=fitz.PDF_ENCRYPT_NONE,
            deflate=True
        )

    new_size = os.path.getsize(output_path)
    return {
        "status": "success",
        "original_size": original_size,
        "new_size": new_size,
        "total_pages": total_pages,
        "output_path": output_path
    }

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Argumen tidak lengkap."}))
        sys.exit(1)

    if sys.argv[1] == "--check":
        if len(sys.argv) < 3:
            print(json.dumps({"status": "error", "message": "Path PDF diperlukan."}))
            sys.exit(1)
        try:
            res = check_encryption(os.path.abspath(sys.argv[2]))
            print(json.dumps(res))
            sys.exit(0)
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
            sys.exit(1)

    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Gunakan: unlock_pdf.py <input_pdf> <output_pdf> [password]"}))
        sys.exit(1)

    input_path = os.path.abspath(sys.argv[1])
    output_path = os.path.abspath(sys.argv[2])
    password = sys.argv[3] if len(sys.argv) > 3 else ""

    try:
        res = unlock_pdf(input_path, output_path, password)
        print(json.dumps(res))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
