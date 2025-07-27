<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationApiController extends Controller
{
    // GET /api/notifications?page=1&per_page=30
    public function index(Request $request)
    {
        $user = auth()->user();
        $perPage = (int)($request->input('per_page', 30));
        $page = (int)($request->input('page', 1));

        $notifQuery = Notification::forUser($user->id)
            ->orderByDesc('created_at');

        $notifications = $notifQuery->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'has_more' => $notifications->hasMorePages(),
            ]
        ]);
    }

    // PATCH /api/notifications/{id}/read
    public function markAsRead($id)
    {
        $user = auth()->user();
        $notif = Notification::where('id', $id)
            ->forUser($user->id)
            ->firstOrFail();

        $notif->markAsRead();

        return response()->json(['success' => true]);
    }

    // POST /api/notifications/mark-all-read
    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();
        Notification::forUser($user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
