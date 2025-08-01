@extends('layouts.app')

@section('content')

{{-- Banner หลัก --}}
<div class="container-section">
    <div class="theme-bg text-white py-5 banner-curve-all">
        <div class="text-center">
            <h1 class="display-4 fw-bold">บัตรประชาชนใบเดียว ก็ผ่อนได้</h1>
            <div class="lead mb-2 fs-5">ไม่เช็คแบล็คลิสต์ ไม่เช็คบูโร ไม่ใช้คนค้ำ</div>
            <a href="{{ route('gold.index') }}" class="btn btn-theme btn-rounded mt-2">เริ่มต้นผ่อนทองเลย</a>
        </div>
    </div>
</div>

<div class="container-section gold-main-section">
    <div class="gold-box" style="position: relative;">
        {{-- รูปโลโก้ซ้ายบน --}}
        
        <div class="gold-header" style="padding-left:10px;">
            ราคาทองรูปพรรณ 96.5% (ข้อมูลล่าสุดจาก ทองคำราคา.com)
        </div>
        <table class="gold-table" id="goldPriceTable">
            <thead>
                <tr>
                    <th style="background:#fffbe5;color:#730a22;font-size:20px;">96.5%</th>
                    <th style="background:#730a22;color:#fff;font-size:20px;">รับซื้อ</th>
                    <th style="background:#730a22;color:#fff;font-size:20px;">ขายออก</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="type text-start" style="font-weight:bold;font-size:18px;">ทองรูปพรรณ</td>
                    <td class="price" style="font-size:22px;font-weight:bold;color:#d33;">
                        @if($goldPrices && is_numeric($goldPrices['ornament_buy']))
                            {{ number_format($goldPrices['ornament_buy'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="price" style="font-size:22px;font-weight:bold;color:#b41010;">
                        @if($goldPrices && is_numeric($goldPrices['ornament_sell']))
                            {{ number_format($goldPrices['ornament_sell'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="gold-footer" style="text-align:right;padding-bottom:2px;padding-right:10px;">
            @if($goldPrices && !empty($goldPrices['date']))
                <span style="color:#999;font-size:15px;">
                    อัปเดต {{ \Carbon\Carbon::parse($goldPrices['date'].' 09:00')->format('d/m/Y H:i') }}
                </span>
            @else
                <span style="color:#c33;">ไม่สามารถโหลดราคาทองได้</span>
            @endif
        </div>
    </div>
</div>

{{-- จุดเด่น --}}
<div class="container-section">
    <div class="row text-center g-3">
        <div class="col-md-3 col-6">
            <div class="feature-card"><i class="bi bi-person-badge"></i> บัตรประชาชนใบเดียว</div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card"><i class="bi bi-lightning"></i> สะดวก รวดเร็ว</div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card"><i class="bi bi-shield-check"></i> ไม่เช็คเครดิต ไม่เช็คบูโร</div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card"><i class="bi bi-arrow-repeat"></i> การผ่อนคืนสบาย ผ่อนได้จริง</div>
        </div>
    </div>
</div>

{{-- รีวิวลูกค้าจริง --}}
<div class="container-section">
    <div class="section-title">รีวิวบางส่วนจากลูกค้าจริง</div>
    <div class="row g-3">
        @foreach($reviews as $review)
            <div class="col-md-3 col-6">
                <div class="section-card text-center">
                    <div class="mb-2 d-flex justify-content-center align-items-center" style="min-height:140px;">
                        <img src="{{ $review->image_url ? asset('storage/'.$review->image_url) : 'https://placehold.co/140x140/730A22/fff?text=IMG' }}"
                            class="rounded-circle shadow" style="width:120px;height:120px;object-fit:cover;">
                    </div>
                    <div class="fw-bold mb-1">{{ $review->name }}</div>
                    <div style="font-size:16px;line-height:1.3;">{{ $review->text }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- ส่วนที่เหลือเหมือนเดิม --}}

{{-- ขั้นตอนการสมัคร --}}
<div class="container-section">
    <div class="section-title">ขั้นตอนการสมัครผ่อนทอง</div>
    <div class="row text-center mb-4">
        <div class="col-md-4 mb-3">
            <div class="step-circle">1</div>
            <div class="fw-bold">กดยื่นออนไลน์</div>
            <div style="font-size:14px;">แอดไลน์ หรือลงทะเบียนผ่านฟอร์ม</div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="step-circle">2</div>
            <div class="fw-bold">กรอกแบบฟอร์ม</div>
            <div style="font-size:14px;">กรอกข้อมูลพร้อมแนบบัตรประชาชน</div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="step-circle">3</div>
            <div class="fw-bold">รับทอง</div>
            <div style="font-size:14px;">อนุมัติไว รับทองหน้าร้าน หรือจัดส่งถึงบ้าน</div>
        </div>
    </div>
</div>

{{-- คุณสมบัติและเอกสาร --}}
<div class="container-section">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="section-title">คุณสมบัติผู้สมัคร</div>
            <div class="section-card">
                <div class="fw-bold">คุณสมบัติ</div>
                <ul>
                    <li>อายุตั้งแต่ 20 ปีบริบูรณ์ขึ้นไป</li>
                    <li>มีบัตรประชาชน</li>
                    <li>มีรายได้ประจำ/อาชีพอิสระ</li>
                    <li>ไม่ต้องมีคนค้ำประกัน</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="section-title">เอกสารที่ใช้</div>
            <div class="section-card">
                <div class="fw-bold">เอกสารที่ใช้</div>
                <ul>
                    <li>บัตรประชาชน</li>
                    <li>สลิปเงินเดือน/รายการเดินบัญชี</li>
                    <li>ทะเบียนบ้าน (ถ้ามี)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- พื้นที่ให้บริการ --}}
<div class="container-section">
    <div class="section-title">พื้นที่ให้บริการ</div>
    <div class="section-card">
        <ul class="mb-0">
            <li>กรุงเทพฯ</li>
            <li>ปริมณฑล</li>
            <li>ภาคกลาง</li>
            <li>ภาคตะวันออก</li>
            <li>ภาคอีสาน</li>
        </ul>
    </div>
</div>

{{-- FAQ --}}
<div class="container-section">
    <div class="section-title">คำถามที่พบบ่อย (FAQ)</div>
    <div class="accordion" id="faqAccordion">
        @php
        $faqs = [
            ["q" => "คำถามเกี่ยวกับผ่อนทองทั่วไป", "a" => "สามารถผ่อนทองได้โดยใช้บัตรประชาชนใบเดียว"],
            ["q" => "ผ่อนเครื่องใช้ไฟฟ้าได้ไหม", "a" => "ได้ มีบริการผ่อนเครื่องใช้ไฟฟ้า"],
            ["q" => "เงื่อนไขในการผ่อน", "a" => "ไม่ต้องมีคนค้ำ ไม่เช็คเครดิตบูโร"],
            ["q" => "การรับทองที่ไหน", "a" => "รับทองหน้าร้านหรือจัดส่งถึงบ้าน"],
            ["q" => "ทองผ่อนได้ไหม", "a" => "ผ่อนได้โดยใช้บัตรประชาชนใบเดียว"],
        ];
        @endphp
        @foreach($faqs as $i => $faq)
        <div class="accordion-item">
            <h2 class="accordion-header" id="faq{{ $i }}">
                <button class="accordion-button collapsed theme-color" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $i }}">
                    {{ $faq['q'] }}
                </button>
            </h2>
            <div id="collapse{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">{{ $faq['a'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ฟอร์มขอผ่อนทอง --}}
<div class="container-section">
    <div class="section-title">แบบฟอร์มขอผ่อนทอง</div>
    <div class="section-card">
        <form>
            <div class="mb-3">
                <label for="goldType" class="form-label">ประเภททองคำ</label>
                <input type="text" class="form-control" id="goldType" placeholder="เช่น ทองแท่ง">
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">จำนวน (บาท)</label>
                <input type="number" class="form-control" id="amount" placeholder="0">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="accept" checked>
                <label class="form-check-label" for="accept">ข้าพเจ้ายอมรับข้อกำหนดและเงื่อนไข</label>
            </div>
            <button type="submit" class="btn btn-theme">ถัดไป</button>
        </form>
    </div>
</div>

@endsection
