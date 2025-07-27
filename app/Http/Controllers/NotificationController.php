<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user && $user->is_admin ? 'admin' : 'user';

        $query = Notification::where('role', $role);

        if ($role === 'user') {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    public function read(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $user = $request->user();

        if ($notification->user_id && $notification->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $notification->read_at = now();
        $notification->save();

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|integer',
            'role' => 'required|string|in:user,admin',
            'type' => 'required|string',
            'title' => 'required|string',
            'message' => 'required|string',
            'data' => 'nullable|array',
        ]);
        $data['data'] = $data['data'] ?? [];

        $n = Notification::create([
            ...$data,
            'data' => json_encode($data['data']),
        ]);

        return response()->json($n, 201);
    }
}
