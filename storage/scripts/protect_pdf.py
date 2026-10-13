#!/usr/bin/env python3
"""
Protect PDF Document with Military-Grade AES-256 Encryption.
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

def inspect_pdf(pdf_path):
    import fitz
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {pdf_path}")
    
    with fitz.open(pdf_path) as doc:
        return {
            "status": "success",
            "is_encrypted": doc.is_encrypted,
            "needs_pass": doc.needs_pass,
            "total_pages": len(doc),
            "file_size": os.path.getsize(pdf_path)
        }

def protect_pdf(input_path, output_path, user_password, owner_password=None, allow_print=True, allow_copy=True):
    import fitz
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {input_path}")

    if not user_password:
        raise ValueError("Password pembuka dokumen (user password) wajib diisi.")

    if not owner_password:
        owner_password = user_password + "_owner_master"

    original_size = os.path.getsize(input_path)
    output_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(output_dir, exist_ok=True)

    with fitz.open(input_path) as doc:
        if doc.is_encrypted and doc.needs_pass:
            raise ValueError("Dokumen PDF ini sudah terproteksi password sebelumnya. Silakan buka proteksinya terlebih dahulu via tool Unlock PDF.")

        total_pages = len(doc)

        # Calculate permissions bitmask
        perms = 0
        if allow_print:
            perms |= (fitz.PDF_PERM_PRINT | fitz.PDF_PERM_PRINT_HQ)
        if allow_copy:
            perms |= (fitz.PDF_PERM_COPY | fitz.PDF_PERM_ACCESSIBILITY)

        # Save with AES-256 encryption
        doc.save(
            output_path,
            encryption=fitz.PDF_ENCRYPT_AES_256,
            user_pw=str(user_password),
            owner_pw=str(owner_password),
            permissions=perms,
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
        print(json.dumps({"status": "error", "message": "Gunakan: protect_pdf.py <input_pdf> <output_pdf> <user_password> [owner_password] [allow_print] [allow_copy]"}))
        sys.exit(1)

    input_path = os.path.abspath(sys.argv[1])
    output_path = os.path.abspath(sys.argv[2])
    user_password = sys.argv[3]
    owner_password = sys.argv[4] if len(sys.argv) > 4 and sys.argv[4] not in ("", "-") else None
    allow_print = True if len(sys.argv) <= 5 or sys.argv[5] in ("1", "true", "True") else False
    allow_copy = True if len(sys.argv) <= 6 or sys.argv[6] in ("1", "true", "True") else False

    try:
        res = protect_pdf(input_path, output_path, user_password, owner_password, allow_print, allow_copy)
        print(json.dumps(res))
        sys.exit(0)
    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
