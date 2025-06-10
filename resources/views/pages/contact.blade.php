@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg p-4 sm:p-6">
    <h2 class="text-xl sm:text-2xl font-bold text-kplus-green mb-4">ติดต่อเรา</h2>

    <p class="mb-4 text-gray-700">คุณสามารถติดต่อหรือสอบถามรายละเอียดได้ตามช่องทางต่อไปนี้ค่ะ:</p>

    <ul class="list-disc pl-6 text-gray-700">
        <li><strong>Line:</strong> <a href="https://line.me" class="text-kplus-green">@kplusinstallment</a></li>
        <li><strong>Facebook:</strong> <a href="https://facebook.com" class="text-kplus-green">KPLUS ผ่อนง่าย</a></li>
        <li><strong>โทรศัพท์:</strong> <a href="tel:021234567" class="text-kplus-green">02-123-4567</a></li>
        <li><strong>อีเมล:</strong> <a href="mailto:info@kplusinstallment.com" class="text-kplus-green">info@kplusinstallment.com</a></li>
    </ul>

    <div class="mt-6 text-gray-700">
        <h3 class="text-xl font-semibold mb-2">เวลาทำการ</h3>
        <p>วันจันทร์ - ศุกร์: 9.00 - 18.00 น.</p>
        <p>วันเสาร์: 10.00 - 15.00 น.</p>
        <p>ปิดทำการวันอาทิตย์และวันหยุดนักขัตฤกษ์</p>
    </div>

    <div class="mt-6 text-gray-700">
        <h3 class="text-xl font-semibold mb-2">ที่อยู่สำนักงาน</h3>
        <p>บริษัท เคพลัส ผ่อนง่าย จำกัด</p>
        <p>123 อาคารกสิกรไทย ถนนพหลโยธิน</p>
        <p>เขตจตุจักร กรุงเทพฯ 10900</p>
    </div>
</div>
@endsection
