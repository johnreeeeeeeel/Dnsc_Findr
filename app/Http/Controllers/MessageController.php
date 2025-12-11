<?php

namespace App\Http\Controllers;

use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function conversations()
    {
        $userId = Auth::user()->user_id;

        // Get all unique conversation partners
        $partners = PrivateMessage::query()
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as partner_id', [$userId])
            ->selectRaw('MAX(created_at) as last_message_at')
            ->groupBy('partner_id')
            ->orderByDesc('last_message_at')
            ->pluck('partner_id');

        $conversations = $partners
            ->map(function ($partnerId) use ($userId) {
                $partner = User::select('user_id', 'username', 'usertype')->where('user_id', $partnerId)->first();

                if (!$partner) {
                    return null;
                }

                $lastMessage = PrivateMessage::where(function ($q) use ($userId, $partnerId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
                })
                    ->orWhere(function ($q) use ($userId, $partnerId) {
                        $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
                    })
                    ->latest()
                    ->first();

                $unread = PrivateMessage::where('sender_id', $partnerId)->where('receiver_id', $userId)->where('is_read', false)->count();

                return [
                    'partner' => $partner,
                    'display_name' => $partner->username, // use username for display
                    'last_message' => $lastMessage?->message ?? '',
                    'last_time' => $lastMessage?->created_at,
                    'unread_count' => $unread,
                ];
            })
            ->filter()
            ->values();

        return response()->json($conversations);
    }

    public function chatWith($partnerId)
    {
        $userId = Auth::user()->user_id;

        $messages = PrivateMessage::where(function ($q) use ($userId, $partnerId) {
            $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
        })
            ->orWhere(function ($q) use ($userId, $partnerId) {
                $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
            })
            ->with(['sender' => fn($q) => $q->select('user_id', 'username')])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($userId) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_id' => $msg->sender_id,
                    'receiver_id' => $msg->receiver_id,
                    'created_at' => $msg->created_at,
                    'sender' => $msg->sender,
                    'is_sender' => $msg->sender_id === $userId, 
                ];
            });

        // Mark messages as read
        PrivateMessage::where('sender_id', $partnerId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,user_id',
            'message' => 'required|string|max:2000|min:1',
        ]);

        $message = PrivateMessage::create([
            'sender_id' => Auth::user()->user_id,
            'receiver_id' => $request->receiver_id,
            'message' => trim($request->message),
        ]);

        // Load sender info for response (username only)
        $message->load(['sender' => fn($q) => $q->select('user_id', 'username')]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function users(Request $request)
    {
        $query = User::select('user_id', 'username', 'email', 'usertype')->where('user_id', '!=', Auth::user()->user_id);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('username', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%");
            });
        }

        return $query->orderBy('username')->paginate(20);
    }

    public function unreadCount()
    {
        $count = PrivateMessage::where('receiver_id', Auth::user()->user_id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread' => $count]);
    }
}
