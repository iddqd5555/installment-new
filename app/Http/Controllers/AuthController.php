<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $req)
    {
        try {
            $credentials = $req->validate([
                'phone' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            // หา user ตาม phone
            $user = \App\Models\User::where('phone', $req->phone)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'ไม่พบผู้ใช้เบอร์นี้',
                ], 404);
            }

            // ตรวจสอบรหัสผ่านด้วย Hash::check
            if (!\Hash::check($req->password, $user->password)) {
                return response()->json([
                    'message' => 'รหัสผ่านไม่ถูกต้อง',
                ], 401);
            }

            // ถ้า user ถูกปิด
            if ($user->status !== 'active') {
                return response()->json([
                    'message' => 'บัญชีนี้ถูกปิดใช้งาน',
                ], 403);
            }

            // สร้าง Sanctum Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('LOGIN ERROR: '.$e->getMessage().' LINE '.$e->getLine());
            return response()->json([
                'message' => 'Login Server Error: '.$e->getMessage(),
            ], 500);
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
