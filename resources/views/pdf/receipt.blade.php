<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'thsarabunnew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'thsarabunnew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew-Bold.ttf') }}") format('truetype');
        }
        body {
            font-family: 'thsarabunnew', sans-serif;
            font-size: 18pt;
            margin: 10px;
        }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        table { width: 100%; }
        td, th { padding: 2px 0; }
        .footer { font-size: 14pt; text-align: center; margin-top: 18px; }
        hr { margin: 12px 0; }
    </style>

    <!-- เพิ่ม Sunmi Printer JS SDK -->
    <script src="https://sunmi-ota.oss-cn-hangzhou.aliyuncs.com/sdk/v1.0.0/sunmi-printer.js"></script>

    <script>
    function printToSunmi() {
        var receiptText = `
บริษัท วิสดอม โกลด์ กรุ้ป จำกัด
ใบเสร็จรับเงิน
----------------------------------
เลขที่ใบเสร็จ: {{ $payment->ref ?? '-' }}
วันที่: {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}

ลูกค้า: {{ $customer->first_name }} {{ $customer->last_name }}
เบอร์โทร: {{ $customer->phone }}
รหัสสัญญา: {{ $contract->contract_number }}
เลขบัตร: {{ $customer->id_card_number }}

รายการ: ผ่อนทอง {{ $contract->gold_amount }} บาท
ยอดชำระ: {{ number_format($payment->amount, 2) }} บาท

ยอดคงเหลือ: {{ number_format($contract->remaining_amount,2) }} บาท
----------------------------------
ขอบคุณที่ใช้บริการ
บริษัท วิสดอม โกลด์ กรุ้ป จำกัด
`;

        sunmiInnerPrinter.printOriginalText(receiptText, function(success){
            alert("พิมพ์ใบเสร็จเรียบร้อยแล้ว");
        }, function(error){
            alert("ไม่สามารถพิมพ์ใบเสร็จได้: " + error);
        });
    }
    </script>
</head>
<body>
    <div class="text-center">
        <div class="bold">บริษัท วิสดอม โกลด์ กรุ้ป จำกัด</div>
        <div>116 อาคารเอสพี2 ชั้นที่ 2 ห้องเลขที่ 2/1 หมู่ที่ 2 ตำบลละหาร อำเภอบางบัวทอง จังหวัดนนทบุรี 11110</div>
        <div>เลขประจำตัวผู้เสียภาษี 0125567040961</div>
        <div>โทร. 02-379-1102</div>
        <hr>
    </div>
    <table>
        <tr>
            <td>เลขที่ใบเสร็จ: <b>{{ $payment->ref ?? '-' }}</b></td>
            <td>วันที่: <b>{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}</b></td>
        </tr>
        <tr>
            <td colspan="2">ลูกค้า: <b>{{ $customer->first_name }} {{ $customer->last_name }}</b> ({{ $customer->phone }})</td>
        </tr>
        <tr>
            <td>รหัสสัญญา: <b>{{ $contract->contract_number }}</b></td>
            <td>เลขบัตร: {{ $customer->id_card_number }}</td>
        </tr>
    </table>
    <hr>
    <div>
        <b>รายการ:</b>
        <table>
            <tr>
                <td>ผ่อนทอง {{ $contract->gold_amount }} บาท</td>
                <td align="right">{{ number_format($payment->amount, 2) }} บาท</td>
            </tr>
        </table>
    </div>
    <hr>
    <table>
        <tr>
            <td>ยอดงวดนี้:</td>
            <td align="right"><b>{{ number_format($payment->amount,2) }}</b> บาท</td>
        </tr>
        <tr>
            <td>ยอดคงเหลือ:</td>
            <td align="right"><b>{{ number_format($contract->remaining_amount,2) }}</b> บาท</td>
        </tr>
    </table>
    <div class="footer">
        <div>ขอบคุณที่ใช้บริการ</div>
        <div>บริษัท วิสดอม โกลด์ กรุ้ป จำกัด</div>
    </div>
</body>
</html>
