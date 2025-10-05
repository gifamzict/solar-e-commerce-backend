<?php

// In app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered; // <-- Import the Registered event
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Access\AuthorizationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'role' defaults to 0 (User) in the database migration, no need to pass it here.
        ]);

        // 3. Trigger Email Verification Event
        // This event sends the verification link (built-in Laravel feature).
        event(new Registered($user));

        // 4. Generate Token (Optional but good practice for API)
        // We're returning a simple message now, but for a production API,
        // you often log them in immediately and send a token.
        // For now, let's keep it clean for verification.

        return response()->json([
            'message' => 'User registered successfully. Please check your email for the verification link.',
            'user' => $user->only(['name', 'email', 'role']),
        ], 201);
    }

    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $id, (string) $user->getKey())) {
            throw new AuthorizationException;
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($user->hasVerifiedEmail()) {
            return view('email-verified', ['message' => 'Email already verified.']);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return view('email-verified', ['message' => 'Email verified successfully.']);
    }

    public function login(Request $request)
    {
        // 1. Validation
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 2. Attempt to authenticate the user
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // 3. Get the authenticated user
        $user = auth()->user();

        // 4. Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email before logging in.',
            ], 403);
        }

        // 5. Create a token
        $token = $user->createToken('API Token')->plainTextToken;

        // 6. Return the token and user info
        return response()->json([
            'message' => 'Login successful.',
            'user' => $user->only(['name', 'email', 'role']),
            'token' => $token,
        ], 200);
    }

    // You will add logout methods here later...
}
