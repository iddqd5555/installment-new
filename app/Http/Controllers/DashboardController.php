<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $installmentRequests = InstallmentRequest::with(['installmentPayments'])->where('status', 'approved')->get();

        // บังคับให้โหลดข้อมูลล่าสุดจากฐานข้อมูล
        $installmentRequests->each(function ($request) {
            $request->refresh();
            $request->load('installmentPayments');
        });

        $bankAccounts = BankAccount::all();
        $payments = InstallmentPayment::latest()->take(10)->get();

        return view('dashboard', compact('installmentRequests', 'bankAccounts', 'payments'));
    }

}
