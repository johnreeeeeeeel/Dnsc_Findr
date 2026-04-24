<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with('user')
            ->latest('submitted_at')
            ->get()
            ->map(function ($fb) {
                if ($fb->is_anonymous) {
                    $fb->user = (object) [
                        'firstname' => 'Anonymous',
                        'lastname' => '',
                        'user_id' => '—',
                    ];
                }
                return $fb;
            });

        return response()->json($feedbacks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'is_anonymous' => 'sometimes|boolean',
            'service_used' => 'required|in:Claim Lost,Report Found',
            'rating_system' => 'required|integer|min:1|max:5',
            'rating_service' => 'required|integer|min:1|max:5',
            'message_system' => 'required|string|min:10',
            'message_service' => 'required|string|min:10',
        ]);

        Feedback::create([
            'user_id' => Auth::id(),
            'is_anonymous' => $request->boolean('is_anonymous', false),
            'service_used' => $request->service_used,
            'rating_system' => $request->rating_system,
            'rating_service' => $request->rating_service,
            'message_system' => $request->message_system,
            'message_service' => $request->message_service,
        ]);

        return response()->json(['message' => 'Thank you for your feedback!'], 201);
    }

    public function destroy($id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback not found'], 404);
        }

        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted successfully']);
    }
}
