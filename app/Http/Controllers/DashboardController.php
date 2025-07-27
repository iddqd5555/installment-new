<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // แยกตาม role
        if ($user->role === 'staff') {
            $installmentRequests = InstallmentRequest::with('installmentPayments')
                ->where('responsible_staff', $user->username)
                ->where('status', 'approved')
                ->get();
        } else {
            // admin เห็นหมด
            $installmentRequests = InstallmentRequest::with('installmentPayments')
                ->where('status', 'approved')->get();
        }

        // คำนวณ widget
        $today = now()->format('Y-m-d');
        $pendingToday = InstallmentPayment::whereHas('installmentRequest', function ($q) use ($user) {
            if ($user->role === 'staff') {
                $q->where('responsible_staff', $user->username);
            }
            $q->where('status', 'approved');
        })
        ->where('payment_due_date', $today)
        ->where('status', 'pending')
        ->sum('amount');

        $waitingApprove = InstallmentRequest::where(function ($q) use ($user) {
            if ($user->role === 'staff') {
                $q->where('responsible_staff', $user->username);
            }
        })->whereIn('status', ['pending', 'staff_approved'])->count();

        $overdue = InstallmentPayment::whereHas('installmentRequest', function ($q) use ($user) {
            if ($user->role === 'staff') {
                $q->where('responsible_staff', $user->username);
            }
        })->where('status', 'pending')
            ->where('payment_due_date', '<', $today)
            ->count();

        $bankAccounts = BankAccount::all();
        $payments = InstallmentPayment::latest()->take(10)->get();

        return view('dashboard', compact(
            'installmentRequests',
            'bankAccounts',
            'payments',
            'pendingToday',
            'waitingApprove',
            'overdue'
        ));
    }
}
