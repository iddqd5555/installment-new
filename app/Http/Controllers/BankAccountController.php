<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::all();
        return view('bank-accounts.index', compact('bankAccounts'));
    }
}
