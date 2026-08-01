import sys
from pdf2docx import Converter
import os

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
