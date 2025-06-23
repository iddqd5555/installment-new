<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentStaffController extends Controller
{
    public function index()
    {
        $payments = Payment::latest()->get();
        return view('staff.payments.index', compact('payments'));
    }

    public function create()
    {
        return view('staff.payments.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $payment = Payment::findOrFail($id);
        return view('staff.payments.show', compact('payment'));
    }

    public function edit($id)
    {
        $payment = Payment::findOrFail($id);
        return view('staff.payments.edit', compact('payment'));
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        Payment::destroy($id);
        return redirect()->route('staff.payments.index');
    }
}
