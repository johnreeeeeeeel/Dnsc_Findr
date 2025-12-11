<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        // Validation
        $request->validate([
            'current_password'          => 'required',
            'new_password'              => 'required|min:8',
            'new_password_confirmation' => 'required|same:new_password', // Must match
        ]);

        // Determine which password to check (temp or real)
        $currentHash = $user->hasTempPassword() 
            ? $user->temp_password_hash 
            : $user->password;

        // Wrong current password
        if (!Hash::check($request->current_password, $currentHash)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect!'
            ], 400);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->temp_password_hash = null; 
        $user->save();

        session()->forget('temp_password_skipped_prompt');

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!'
        ]);
    }

    public function skipTempPassChange(Request $request)
    {
        if (Auth::user()->hasTempPassword()) {
            session(['temp_password_skipped_prompt' => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}