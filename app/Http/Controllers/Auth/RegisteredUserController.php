<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'surname' => 'required|string|max:255',
        'phone' => 'required|unique:users',
        'password' => 'required|min:6',
        'id_card_number' => 'required|unique:users',

        'id_card_image' => 'required|image|max:2048',
        'house_registration_image' => 'nullable|image|max:2048',
        'business_registration_image' => 'nullable|image|max:2048',
        'bank_statement_image' => 'nullable|image|max:2048',
        'bank_account_image' => 'nullable|image|max:2048',
        'staff_reference' => 'nullable|string',
    ]);

    $user = User::create([
        'name' => $request->name,
        'surname' => $request->surname,
        'phone' => $request->phone,
        'password' => bcrypt($request->password),
        'id_card_number' => $request->id_card_number,
        'staff_reference' => $request->staff_reference,
        'id_card_image' => $request->file('id_card_image')->store('id_cards', 'public'),
        'house_registration_image' => $request->file('house_registration_image')?->store('house_registrations', 'public'),
        'business_registration_image' => $request->file('business_registration_image')?->store('business_registrations', 'public'),
        'bank_statement_image' => $request->file('bank_statement_image')?->store('bank_statements', 'public'),
        'bank_account_image' => $request->file('bank_account_image')?->store('bank_accounts', 'public'),
    ]);

    return redirect()->route('login')->with('success', 'สมัครสมาชิกเรียบร้อย รอการอนุมัติจากแอดมินค่ะ');
}

}
