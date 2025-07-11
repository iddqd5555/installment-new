from pyzbar.pyzbar import decode
from PIL import Image
import sys

def read_qr(image_path):
    img = Image.open(image_path)
    decoded_objects = decode(img)
    for obj in decoded_objects:
        print(obj.data.decode('utf-8'))
        return
    print('') # ถ้าไม่พบ QR

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print('Usage: python read_qr.py <image_path>')
        exit(1)
    read_qr(sys.argv[1])
