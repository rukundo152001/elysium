<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function login(Request $request)
    {
        Log::info("LOGIN ATTEMPT STARTED", [
            'email' => $request->email,
            'user_type' => $request->user_type,
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);

        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
                'user_type' => 'required|in:staff,admin,doctor,cashier',
            ]);
        } catch (ValidationException $e) {
            Log::warning("LOGIN FAILED: Validation Error", [
                'email' => $request->email,
                'user_type' => $request->user_type,
                'errors' => $e->errors(),
                'ip' => $request->ip()
            ]);
            throw $e;
        }

        Log::info("VALIDATION PASSED", [
            'email' => $request->email,
            'user_type' => $request->user_type
        ]);

        $user = User::where('email', $request->email)->first();

        // If email does not exist
        if (!$user) {
            Log::warning("LOGIN FAILED: Email not found", [
                'email' => $request->email,
                'type' => $request->user_type,
                'ip' => $request->ip()
            ]);

            throw ValidationException::withMessages([
                'email' => ['No account found with this email address.'],
            ]);
        }

        Log::info("USER FOUND", [
            'email' => $user->email,
            'user_id' => $user->id,
            'status' => $user->status,
            'role' => $user->role
        ]);

        // If account is inactive
        if ($user->status !== 'active') {
            Log::warning("LOGIN FAILED: Inactive account attempted", [
                'email' => $user->email,
                'status' => $user->status,
                'ip' => $request->ip()
            ]);

            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact the administrator.'],
            ]);
        }

        // Check password before login
        if (!Hash::check($request->password, $user->password)) {
            Log::warning("LOGIN FAILED: Incorrect password", [
                'email' => $user->email,
                'user_type' => $request->user_type,
                'ip' => $request->ip()
            ]);

            throw ValidationException::withMessages([
                'password' => ['The password you entered is incorrect.'],
            ]);
        }

        Log::info("PASSWORD VERIFIED", [
            'email' => $user->email,
            'user_id' => $user->id
        ]);

        // Define role groups
        $staffRoles = ['staff', 'doctor', 'cashier'];

        // Check role match depending on selected login type
        if ($request->user_type === 'staff') {
            if (!in_array($user->role, $staffRoles)) {
                Log::warning("LOGIN FAILED: Unauthorized staff login attempt", [
                    'email' => $user->email,
                    'attempted_type' => $request->user_type,
                    'actual_role' => $user->role,
                    'ip' => $request->ip()
                ]);

                throw ValidationException::withMessages([
                    'email' => ['You do not have access to Staff login.'],
                ]);
            }
        }

        if ($request->user_type === 'admin') {
            if ($user->role !== 'admin') {
                Log::warning("LOGIN FAILED: Unauthorized admin login attempt", [
                    'email' => $user->email,
                    'attempted_type' => $request->user_type,
                    'actual_role' => $user->role,
                    'ip' => $request->ip()
                ]);

                throw ValidationException::withMessages([
                    'email' => ['Only administrators can access Admin login.'],
                ]);
            }
        }

        Log::info("ROLE AUTHORIZATION PASSED", [
            'email' => $user->email,
            'role' => $user->role,
            'login_type' => $request->user_type
        ]);

        // Everything OK — proceed with login
        Auth::login($user);
        $request->session()->regenerate();

        Log::info("LOGIN SUCCESSFUL", [
            'email' => $user->email,
            'role' => $user->role,
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);

        // Redirect based on actual role
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.index'))->with('success', 'Welcome back, ' . $user->name . '!');
        }

        if (in_array($user->role, $staffRoles)) {
            return redirect()->intended(route('staff.index'))->with('success', 'Welcome back, ' . $user->name . '!');
        }

        Log::error("LOGIN: UNEXPECTED ROLE", [
            'email' => $user->email,
            'role' => $user->role
        ]);

        return redirect()->back()->with('error', 'You are not allowed to access this area!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            Log::info("USER LOGOUT", [
                'email' => Auth::user()->email,
                'user_id' => Auth::user()->id,
                'timestamp' => now()
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
