<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\FoundItem;

class AdminDashboardController extends Controller
{
    // Return dashboard stats
    public function stats()
    {
        $today = Carbon::today();

        return response()->json([
            // Users
            'totalUsers'    => User::count(),
            'students'      => User::where('usertype', 'student')->count(),
            'instructors'   => User::where('usertype', 'instructor')->count(),
            'staff'         => User::where('usertype', 'staff')->count(),

            // Found Items / Posts
            'totalPosts'    => FoundItem::count(),
            'pendingPosts'  => FoundItem::whereIn('status', ['pending'])->orWhereIn('post_status', ['pending'])->count(),
            'approvedPosts' => FoundItem::whereIn('status', ['approved'])->orWhereIn('post_status', ['approved'])->count(),
            'postsToday'    => FoundItem::whereDate('created_at', $today)->count(),
        ]);
    }

    // Return recent users (exclude Admins)
    public function recentUsers()
    {
        return User::select('user_id', 'firstname', 'middlename', 'lastname', 'email', 'usertype', 'created_at')
            ->where('usertype', '!=', 'Admin')
            ->latest()
            ->take(6)
            ->get();
    }
}
