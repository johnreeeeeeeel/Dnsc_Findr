<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FoundItem;
use App\Models\User;

class AdminUserController extends Controller
{
    public function getUser()
    {
        $user = Auth::user();

        // return full user info
        return response()->json([
            'user_id' => $user->user_id,
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'lastname' => $user->lastname,
            'fullname' => trim($user->firstname . ' ' . $user->middlename . ' ' . $user->lastname),
            'sex' => $user->sex,
            'dob' => $user->dob,
            'email' => $user->email,
            'username' => $user->username,
            'institute' => $user->institute,
            'program' => $user->program,
            'usertype' => $user->usertype,
        ]);
    }
}
