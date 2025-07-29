<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\ActivityLogger;

class AuthController extends Controller
{
    public function login(Request $req)
    {
        try {
            $credentials = $req->validate([
                'phone' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            $user = User::where('phone', $req->phone)->first();
            if (!$user) return response()->json(['message' => 'ไม่พบผู้ใช้เบอร์นี้'], 404);
            if (!\Hash::check($req->password, $user->password))
                return response()->json(['message' => 'รหัสผ่านไม่ถูกต้อง'], 401);
            if ($user->status !== 'active')
                return response()->json(['message' => 'บัญชีนี้ถูกปิดใช้งาน'], 403);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Log event สำคัญ (login)
            ActivityLogger::log(
                $user,
                'login',
                $req->ip(),
                $req->input('lat'),
                $req->input('lng'),
                $req->input('is_mocked') ? 'mocked' : 'normal',
                'เข้าสู่ระบบ'
            );
            // Update ตำแหน่งล่าสุด
            $user->latitude = $req->input('lat');
            $user->longitude = $req->input('lng');
            $user->location_updated_at = now();
            $user->save();

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('LOGIN ERROR: '.$e->getMessage().' LINE '.$e->getLine());
            return response()->json(['message' => 'Login Server Error: '.$e->getMessage()], 500);
        }
    }
    public function register(Request $req)
    {
        $req->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'first_name' => $req->first_name,
            'last_name' => $req->last_name,
            'phone' => $req->phone,
            'password' => bcrypt($req->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user,
        ], 201);
    }
}
