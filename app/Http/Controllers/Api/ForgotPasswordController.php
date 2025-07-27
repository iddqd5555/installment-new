<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ForgotPasswordController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['success' => false, 'message' => 'ไม่พบผู้ใช้นี้'], 404);

        $otp = rand(100000, 999999);
        Cache::put('otp_' . $request->email, $otp, now()->addMinutes(10));

        // ส่งอีเมล OTP
        try {
            Mail::raw("รหัส OTP สำหรับรีเซ็ตรหัสผ่านของคุณ: $otp", function ($msg) use ($request) {
                $msg->to($request->email)->subject('OTP สำหรับรีเซ็ตรหัสผ่าน');
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'ส่งอีเมลไม่สำเร็จ'], 500);
        }

        return response()->json(['success' => true, 'message' => 'ส่งรหัส OTP ไปยังอีเมลแล้ว']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $cacheOtp = Cache::get('otp_' . $request->email);
        if (!$cacheOtp || $cacheOtp != $request->otp) {
            return response()->json(['success' => false, 'message' => 'OTP ไม่ถูกต้องหรือหมดอายุ'], 422);
        }
        return response()->json(['success' => true]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|min:6',
        ]);
        $cacheOtp = Cache::get('otp_' . $request->email);
        if (!$cacheOtp || $cacheOtp != $request->otp) {
            return response()->json(['success' => false, 'message' => 'OTP ไม่ถูกต้องหรือหมดอายุ'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['success' => false, 'message' => 'ไม่พบผู้ใช้นี้'], 404);

        $user->password = Hash::make($request->password);
        $user->save();

        Cache::forget('otp_' . $request->email);

        return response()->json(['success' => true, 'message' => 'รีเซ็ตรหัสผ่านสำเร็จ']);
    }
}
