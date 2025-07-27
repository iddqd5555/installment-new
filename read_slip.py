from PIL import Image
import pytesseract
import re
import sys
import json
import hashlib
from pyzbar.pyzbar import decode
import codecs
import difflib
import traceback

pytesseract.pytesseract.tesseract_cmd = r"C:\\Program Files\\Tesseract-OCR\\tesseract.exe"

COMPANY_NAMES = [
    "วิสดอม โกลด์ กรุ้ป", "วิสดอม โกลด์ กรุ๊ป", "WISDOM GOLD", "WISDOM GOLD GROUP",
    "WISDOMGOLD", "บริษัท วิสดอม โกลด์ กรุ้ป จำกัด", "WISDOM GOLD GROUP CO., LTD.",
    "บจก.วิสดอม โกลด์ กรุ้ป", "บจก. วิสดอม โกลด์ กรุ๊ป", "WISDOM GOLD GROUP COMPANY"
]

BANK_PATTERNS = {
    "kbank": ["กสิกร", "KBank", "KASIKORN"],
    "scb": ["SCB", "ไทยพาณิชย์", "SCB Easy"],
    "ktb": ["กรุงไทย", "KTB", "Krungthai"],
    "gsb": ["ออมสิน", "GSB"],
    "bbl": ["กรุงเทพ", "BBL", "Bangkok Bank"],
    "bay": ["กรุงศรี", "BAY", "KRUNGSRI"],
    "ttb": ["TTB", "TMB", "ธนชาต", "Thanachart"],
    "uob": ["UOB"],
    "cimb": ["CIMB"],
    "lhbank": ["LHBank", "แลนด์แอนด์เฮ้าส์"],
    "baac": ["BAAC", "ธ.ก.ส.", "ธนาคารเพื่อการเกษตร"],
    "islamic": ["อิสลาม", "Islamic Bank"]
}

def detect_bank(text):
    for bank, keywords in BANK_PATTERNS.items():
        for kw in keywords:
            if kw.lower() in text.lower():
                return bank
    return "generic"

def extract_account_any(text):
    found = set()
    lines = text.splitlines()
    for idx, line in enumerate(lines):
        if re.search(r"(ไปยัง|บัญชี|ธนาคาร|บริษัท|account|A/C|Payee|Receiver|เลขที่|To:|เข้าบัญชี|ชื่อบัญชี|ชื่อผู้รับ)", line, re.I):
            for offset in [0,1,2]:
                if idx+offset < len(lines):
                    nums = re.findall(r'\d{4,15}', lines[idx+offset])
                    found.update(nums)
    nums2 = re.findall(r'\d{4,15}', text)
    found.update(nums2)
    return sorted(found, key=lambda x: -len(x))

def extract_accounts_from_qr(qr_text):
    if not qr_text:
        return []
    return re.findall(r'\d{4,15}', qr_text)

def extract_amount_and_fee(text, qr_text, image_path):
    amount = "0.00"
    fee = "0.00"
    # (1) หา "ค่าธรรมเนียม" จาก text
    m_fee = re.search(r'ค่าธรรมเนียม[:：]?\s*\d*\s*([\d,]+\.\d{2})', text)
    if m_fee:
        fee = m_fee.group(1).replace(',', '')

    # (2) พยายามหา "จำนวน"/"ยอด"/"Amount" จาก text (amount)
    patterns = [
        r'(?:จำนวนเงิน|ยอดเงิน|จํานวนเงิน|จำนวน|ยอด)\s*[:：]*\s*([\d,]+\.\d{2})',
        r'([\d,]+\.\d{2})\s*(?:บาท|THB|Baht)',
        r'Amount[: ]*([\d,]+\.\d{2})'
    ]
    lines = text.splitlines()
    for line in lines:
        for pat in patterns:
            m = re.search(pat, line, re.I)
            if m:
                amt = m.group(1).replace(',', '')
                if float(amt) > 0.0 and amt != fee:
                    return amt, fee
    # (3) OCR เฉพาะเลขจากภาพ (ภาษาอังกฤษล้วน)
    img = Image.open(image_path)
    text_eng = pytesseract.image_to_string(img, lang='eng')
    matches_eng = re.findall(r'[\d,]+\.\d{2}', text_eng)
    for amt in matches_eng:
        amt_clean = amt.replace(',', '')
        if float(amt_clean) > 0.0 and amt_clean != fee:
            return amt_clean, fee
    # (4) fallback: หาเลข float ที่ไม่ใช่ 0.00 และไม่เท่ากับ fee ตัวแรกใน text
    matches = re.findall(r'[\d,]+\.\d{2}', text)
    for amt in matches:
        amt_clean = amt.replace(',', '')
        if float(amt_clean) > 0.0 and amt_clean != fee:
            return amt_clean, fee
    # (5) fallback จาก QR PromptPay
    if qr_text:
        m_amt = re.search(r'54\d{2}([\d\.]+)', qr_text)
        if m_amt and float(m_amt.group(1)) > 0.0:
            return m_amt.group(1), fee
    return amount, fee

def extract_reference(text, qr_text):
    patterns = [
        r'(?:รหัสอ้างอิง|เลขที่อ้างอิง|Ref(?:erence)?|Transaction|Txn|เลขที่รายการ|หมายเลขอ้างอิง)\s*[:\-]*\s*([A-Za-z0-9\-\_]+)',
        r'([A-Za-z0-9]{10,30})'
    ]
    for pat in patterns:
        m = re.search(pat, text)
        if m:
            return m.group(1)
    if qr_text:
        ref_matches = re.findall(r'[A-Za-z0-9]{10,30}', qr_text)
        if ref_matches:
            return ref_matches[0]
    return ""

def extract_company(text):
    # Fuzzy + Regex
    best = ""
    best_score = 0
    text_normal = text.replace(" ", "").replace(".", "").replace(",", "").lower()
    for cname in COMPANY_NAMES:
        t = cname.replace(" ", "").replace(".", "").replace(",", "").lower()
        score = difflib.SequenceMatcher(None, t, text_normal).ratio()
        if score > best_score:
            best = cname
            best_score = score
        if t in text_normal:
            return cname
    m = re.search(r'(บจก\.|บริษัท)[^\n]{0,30}(วิสดอม.?โกลด์.?กร[ุุ๊้]ป)', text, re.I)
    if m:
        return m.group(0)
    if best_score > 0.57:
        return best
    return ""

def get_md5(file_path):
    with open(file_path, "rb") as f:
        return hashlib.md5(f.read()).hexdigest()

def extract_qr(image_path):
    try:
        img = Image.open(image_path)
        qr = decode(img)
        if not qr:
            return ''
        return qr[0].data.decode('utf-8')
    except Exception:
        return ''

def parse_slip(image_path):
    try:
        img = Image.open(image_path)
        text = pytesseract.image_to_string(img, lang='tha+eng')
        qr_text = extract_qr(image_path)
        bank = detect_bank(text)
        # เอาเลขบัญชีจาก QR มาก่อน
        accounts_qr = extract_accounts_from_qr(qr_text)
        accounts_text = extract_account_any(text)
        all_accounts = list(dict.fromkeys(accounts_qr + accounts_text))
        account_main = all_accounts[0] if all_accounts else ""
        amount, fee = extract_amount_and_fee(text, qr_text, image_path)
        reference = extract_reference(text, qr_text)
        company = extract_company(text)
        slip_hash = get_md5(image_path)
        result = {
            "bank": bank,
            "accounts_found": all_accounts,
            "account": account_main if account_main else "",
            "reference": reference if reference else "",
            "amount": amount if amount else "0.00",
            "fee": fee if fee else "0.00",
            "company": company,
            "raw_text": text,
            "qr_text": qr_text,
            "slip_hash": slip_hash
        }
        sys.stdout = codecs.getwriter("utf-8")(sys.stdout.detach())
        print(json.dumps(result, ensure_ascii=False))
    except Exception as e:
        err_result = {
            "error": str(e),
            "traceback": traceback.format_exc()
        }
        sys.stdout = codecs.getwriter("utf-8")(sys.stdout.detach())
        print(json.dumps(err_result, ensure_ascii=False))

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print('{"error": "No image path"}')
        exit(1)
    parse_slip(sys.argv[1])
