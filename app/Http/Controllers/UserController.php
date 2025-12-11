<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\FoundItem;

class UserController extends Controller
{
    // LOGIN

    public function login()
    {
        return view('Login.login');
    }

    public function logincheck(Request $request)
    {
        try {
            $request->validate(
                [
                    'email' => 'required|email',
                    'temppassword' => 'required|min:8',
                ],
                [
                    // VALIDATION MESSAGES
                    'email.required' => 'Please enter your email address.',
                    'email.email' => 'Please enter a valid email format.',
                    'temppassword.required' => 'Please enter your password.',
                    'temppassword.min' => 'Password must be at least 8 characters.',
                ],
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    'errors' => $e->errors(),
                ],
                422,
            );
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['errors' => ['Email not found']], 422);
        }

        // TEMP PASSWORD LOGIN
        if ($user->hasTempPassword()) {
            if (!Hash::check($request->temppassword, $user->temp_password_hash)) {
                return response()->json(['errors' => ['Incorrect temporary password']], 422);
            }

            Auth::login($user);
            $request->session()->regenerate();
            session(['show_change_password_modal' => true]);

            return response()->json(['success' => true]);
        }

        // NORMAL PASSWORD LOGIN
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->temppassword])) {
            return response()->json(['errors' => ['Incorrect password']], 422);
        }

        $request->session()->regenerate();
        session()->forget('show_change_password_modal');

        return response()->json(['success' => true]);
    }

    public function redirectToIndex()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return Auth::user()->usertype === 'Admin' ? view('Admin.dashboard') : view('User.home');
    }

    // ADD USER

    public function adduser()
    {
        return view('Admin.add-user');
    }

    public function addusercheck(Request $request)
    {
        $rules = [
            'usertype' => 'required|string|in:Admin,Instructor,Staff,Student',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
        ];

        // Conditional rules for non-admin users
        if (in_array($request->usertype, ['Instructor', 'Staff', 'Student'])) {
            $rules = array_merge($rules, [
                'lastname' => 'required|string|max:255',
                'firstname' => 'required|string|max:255',
                'sex' => 'required|string|max:10',
                'dob' => 'required|date|before:tomorrow',
            ]);
        }

        // Conditional rules for institute/program
        if (in_array($request->usertype, ['Admin', 'Instructor', 'Staff'])) {
            $rules['institute'] = 'nullable|string|max:255';
            $rules['program'] = 'nullable|string|max:255';
        } elseif ($request->usertype === 'Student') {
            $rules['institute'] = 'required|string|max:255';
            $rules['program'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Temporary password generation
        $tempPassword = 'DF' . rand(1000, 9999) . '-' . rand(1000, 9999) . 'TMP';
        $userId = 'DFNDR' . rand(1000, 9999) . '-' . rand(1000, 9999);
        $tempHash = Hash::make($tempPassword);

        $user = User::create([
            'user_id' => $userId,
            'lastname' => $validated['lastname'] ?? null,
            'firstname' => $validated['firstname'] ?? null,
            'middlename' => $request->middlename ?? null,
            'sex' => $validated['sex'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'usertype' => $validated['usertype'],
            'institute' => $validated['institute'] ?? null,
            'program' => $validated['program'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => null,
            'temp_password_hash' => $tempHash,
        ]);

        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'temp_password' => $tempPassword,
        ]);
    }

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

    // DETAILS USER

    public function userDetails()
    {
        $users = User::select(['user_id', 'username', 'lastname', 'firstname', 'middlename', 'sex', 'dob', 'usertype', 'institute', 'program', 'email', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->makeHidden(['password', 'temp_password_hash', 'remember_token']); // just in case

        return response()->json($users);
    }

    // EDIT USER

    public function editUser(Request $request, $user_id)
    {
        $user = User::where('user_id', $user_id)->firstOrFail();

        $rules = [
            'usertype' => 'required|string|in:Admin,Instructor,Staff,Student',
            'username' => 'required|string|max:50|unique:users,username,' . $user_id . ',user_id',
            'email' => 'required|email|max:255|unique:users,email,' . $user_id . ',user_id',
        ];

        // Conditional rules for non-admin users
        if (in_array($request->usertype, ['Instructor', 'Staff', 'Student'])) {
            $rules = array_merge($rules, [
                'lastname' => 'required|string|max:255',
                'firstname' => 'required|string|max:255',
                'sex' => 'required|string|max:10',
                'dob' => 'required|date|before:tomorrow',
            ]);
        }

        // Conditional rules for institute/program
        if (in_array($request->usertype, ['Admin', 'Instructor', 'Staff'])) {
            $rules['institute'] = 'nullable|string|max:255';
            $rules['program'] = 'nullable|string|max:255';
        } elseif ($request->usertype === 'Student') {
            $rules['institute'] = 'required|string|max:255';
            $rules['program'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $user->update([
            'lastname' => $validated['lastname'] ?? null,
            'firstname' => $validated['firstname'] ?? null,
            'middlename' => $request->middlename ?? null,
            'sex' => $validated['sex'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'usertype' => $validated['usertype'],
            'institute' => $validated['institute'] ?? null,
            'program' => $validated['program'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        return response()->json(['success' => true]);
    }

    // RESET PASSWORD

    public function resetPassword($user_id)
    {
        $user = User::where('user_id', $user_id)->firstOrFail();

        // Generate new temporary password
        $part1 = random_int(1000, 9999);
        $part2 = random_int(1000, 9999);
        $tempPassword = "DF{$part1}-{$part2}TMP";

        // Hash temp password
        $tempHash = Hash::make($tempPassword);

        // Overwrite old password and store temp password hash
        $user->update([
            'password' => null, // remove any existing permanent password
            'temp_password_hash' => $tempHash,
        ]);

        return response()->json([
            'success' => true,
            'user_id' => $user->user_id,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'temp_password' => $tempPassword,
            'message' => 'Password reset successfully',
        ]);
    }

    // DELETE USER

    public function deleteUser($user_id)
    {
        $user = User::where('user_id', $user_id)->firstOrFail();
        $user->delete();

        return response()->json(['success' => true]);
    }

    // LOGOUT

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        session()->forget('temp_password_skipped');

        return redirect()->route('login');
    }
}
