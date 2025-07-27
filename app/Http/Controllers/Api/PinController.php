<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    public function setPin(Request $request)
    {
        $request->validate([
            'pin_code' => 'required|digits:6',
        ]);

        $user = $request->user();
        $user->pin_code = Hash::make($request->pin_code);
        $user->save();

        return response()->json(['success' => true, 'message' => 'ตั้ง PIN สำเร็จ']);
    }

    public function checkPin(Request $request)
    {
        $request->validate([
            'pin_code' => 'required|digits:6',
        ]);

        $user = $request->user();

        // ถ้ายังไม่เคยตั้ง PIN
        if (empty($user->pin_code)) {
            return response()->json(['success' => false, 'message' => 'ยังไม่ได้ตั้ง PIN'], 422);
        }

        // ถ้า PIN ผิด
        if (!Hash::check($request->pin_code, $user->pin_code)) {
            return response()->json(['success' => false, 'message' => 'PIN ไม่ถูกต้อง'], 422);
        }

        // ถูกต้อง
        return response()->json(['success' => true]);
    }
}
