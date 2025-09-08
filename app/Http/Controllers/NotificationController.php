<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    // GET /api/v1/notifications
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // simple structure: unread first, then read
        return response()->json([
            'unread' => $user->unreadNotifications()->orderByDesc('created_at')->get(),
            'read'   => $user->readNotifications()->orderByDesc('created_at')->limit(50)->get(),
        ]);
    }

    // POST /api/v1/notifications/{id}/read
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $n = $user->notifications()->where('id', $id)->firstOrFail();
        if ($n->read_at === null) {
            $n->markAsRead();
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    // POST /api/v1/notifications/read-all
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    // DELETE /api/v1/notifications/{id}
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
