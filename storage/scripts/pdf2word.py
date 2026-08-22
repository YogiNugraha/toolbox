import sys
import os
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

from pdf2docx import Converter

if len(sys.argv) < 3:
    print("Usage: python pdf2word.py <input.pdf> <output_dir>")
    sys.exit(1)

input_pdf = sys.argv[1]
output_dir = sys.argv[2]

if not os.path.exists(input_pdf):
    print(f"Error: Input file does not exist: {input_pdf}")
    sys.exit(1)

if not os.path.exists(output_dir):
    os.makedirs(output_dir, exist_ok=True)

# Generate output path
filename_ext = os.path.basename(input_pdf)
filename = os.path.splitext(filename_ext)[0]
output_docx = os.path.join(output_dir, f"{filename}.docx")

try:
    # Convert PDF to DOCX
    cv = Converter(input_pdf)
    cv.convert(output_docx)      # all pages by default
    cv.close()
    print(f"Success: {output_docx}")
except Exception as e:
    print(f"Error converting PDF to DOCX: {e}")
    sys.exit(1)
