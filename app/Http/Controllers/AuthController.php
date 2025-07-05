<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $req)
    {
        // ตรวจสอบเบอร์และรหัสผ่าน
        $credentials = $req->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // สร้าง Token สำหรับ Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid phone number or password',
        ], 401);
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
