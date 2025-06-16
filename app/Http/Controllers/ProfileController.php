<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile', ['user' => $request->user()]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'workplace' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
            'bank_account_name' => 'nullable|string',
            'id_card_image' => 'nullable|image|max:2048',
            'slip_salary_image' => 'nullable|image|max:2048',
            'additional_documents' => 'nullable|file|max:2048',
        ]);

        if ($request->hasFile('id_card_image')) {
            $data['id_card_image'] = $request->file('id_card_image')->store('id_cards', 'public');
        }

        if ($request->hasFile('slip_salary_image')) {
            $data['slip_salary_image'] = $request->file('slip_salary_image')->store('salary_slips', 'public');
        }

        if ($request->hasFile('additional_documents')) {
            $data['additional_documents'] = $request->file('additional_documents')->store('documents', 'public');
        }

        $user->update($data);

        return Redirect::route('profile.edit')->with('status', 'อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้วค่ะ');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
