from PIL import Image
import pytesseract
import re
import sys
import json

pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

def parse_slip(image_path):
    img = Image.open(image_path)
    text = pytesseract.image_to_string(img, lang='tha+eng')

    acc_match = re.search(r'(865-?1-?00811-?6|8651008116|002-?1-?503541|0021503541)', text.replace(' ', ''))
    account = acc_match.group(1) if acc_match else ''

    ref_match = re.search(r'รหัสอ้างอิง[:\s]*([A-Za-z0-9]+)', text)
    reference = ref_match.group(1) if ref_match else ''

    amt_match = re.search(r'จำนวนเงิน\s*([0-9,]+\.\d{2})', text)
    amount = amt_match.group(1).replace(',', '') if amt_match else ''

    company_match = re.search(r'วิสดอม\s*โกลด์\s*กรุ๊ป', text)
    company = company_match.group(0) if company_match else ''

    result = {
        "account": account,
        "reference": reference,
        "amount": amount,
        "company": company,
        "raw_text": text
    }
    print(json.dumps(result, ensure_ascii=False))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print('{"error": "No image path"}')
        exit(1)
    parse_slip(sys.argv[1])
