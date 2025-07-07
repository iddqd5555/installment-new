<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->all();

        $validator = Validator::make($data, [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            // เพิ่ม validation field อื่นๆได้ตาม DB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user profile fields
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->phone = $data['phone'];
        $user->email = $data['email'];
        $user->address = $data['address'] ?? null;
        $user->date_of_birth = $data['date_of_birth'] ?? null;
        $user->gender = $data['gender'] ?? null;
        // เพิ่มฟิลด์อื่นๆ (workplace, salary, bank_xxx)
        // ....

        // อัปโหลดไฟล์
        if ($request->hasFile('id_card_image')) {
            $filename = 'idcard_' . $user->id . '_' . time() . '.' . $request->file('id_card_image')->getClientOriginalExtension();
            $path = $request->file('id_card_image')->storeAs('uploads', $filename, 'public');
            $user->id_card_image = $filename;
        }

        $user->save();

        return response()->json(['message' => 'Profile updated', 'user' => $user]);
    }
}
