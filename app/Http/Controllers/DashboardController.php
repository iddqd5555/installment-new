<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;

class DashboardController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลสินค้าทั้งหมดที่อนุมัติแล้วมาแสดง
        $products = InstallmentRequest::where('status', 'approved')->get();

        return view('dashboard', compact('products'));
    }
}
