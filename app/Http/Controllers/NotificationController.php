<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notifications;

class NotificationController extends Controller
{
    public function getUserNotifications()
    {
        $userId = Auth::user()->user_id;

        $notifications = Notifications::where('user_id', $userId)->orderBy('created_at', 'desc')->get();

        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $userId = auth()->user()->user_id;

        $notification = Notifications::where('id', $id)->where('user_id', $userId)->first();

        if ($notification) {
            $notification->is_read = true;
            $notification->save();
            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    public function markAllAsRead()
    {
        $userId = auth()->user()->user_id;

        // Update all unread notifications to read
        Notifications::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function deleteNotification($id)
    {
        $userId = auth()->user()->user_id;
        $notification = Notifications::where('id', $id)->where('user_id', $userId)->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();
        return response()->json(['message' => 'Notification deleted']);
    }

    public function deleteAllNotifications()
    {
        $userId = auth()->user()->user_id;
        Notifications::where('user_id', $userId)->delete();

        return response()->json(['message' => 'All notifications deleted']);
    }
}
