from PIL import Image
import pytesseract
import re
import sys
import json

def parse_slip(image_path):
    img = Image.open(image_path)
    text = pytesseract.image_to_string(img, lang='tha+eng')
    # หาบัญชีบริษัท (xxx-xxx8116, x-8116, 8651008116)
    acc_match = re.search(r'(8[0-9]{2,}-?[0-9]{3,}-?8116|x-?8116|8651008116)', text)
    account = acc_match.group(1) if acc_match else ''
    # Reference (SCB/Kbank/BBL)
    ref_match = re.search(r'รหัส[อ้]?างอิง[:\s]+([A-Za-z0-9]+)', text)
    reference = ref_match.group(1) if ref_match else ''
    # จำนวนเงิน
    amt_match = re.search(r'จำนวน.?เงิน\s*([0-9,\.]+)', text)
    amount = amt_match.group(1).replace(',', '') if amt_match else ''
    # ธนาคารปลายทาง (ดักชื่อบริษัท)
    to_match = re.search(r'บจก.?\.?\s*วิสดอม.?โกลด์.?กรุ้ป', text)
    company = to_match.group(0) if to_match else ''
    # RAW text
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
