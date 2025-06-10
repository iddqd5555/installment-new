@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 shadow-lg rounded-lg text-center">
    <h1 class="text-2xl sm:text-3xl font-bold text-kplus-green mb-4">KPLUS ผ่อนง่าย สบายกระเป๋า!</h1>
    <p class="text-gray-700">สมัครง่าย อนุมัติไว แค่ใช้บัตรประชาชนใบเดียว</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
        <a href="/gold" class="bg-kplus-green text-white p-4 rounded-lg shadow hover:bg-green-600">
            ผ่อนทองคำ
        </a>
        <a href="/phone" class="bg-kplus-green text-white p-4 rounded-lg shadow hover:bg-green-600">
            ผ่อนมือถือ
        </a>
    </div>
</div>
@endsection
