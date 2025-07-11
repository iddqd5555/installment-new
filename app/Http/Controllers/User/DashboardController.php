<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InstallmentRequest;
use App\Models\PaymentQrLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $installmentRequests = InstallmentRequest::with([
            'installmentPayments' => function($q) {
                $q->orderBy('payment_due_date', 'asc');
            }
        ])
        ->where('user_id', $user->id)
        ->where('status', 'approved')
        ->orderByDesc('created_at')
        ->get();

        $installment = $installmentRequests->first();

        $qrLogs = PaymentQrLog::where('customer_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('user.dashboard', compact('user', 'installment', 'installmentRequests', 'qrLogs'));
    }
}
