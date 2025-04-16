<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{

    //Register User
    public function register(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Log in the user automatically
        Auth::login($user);

        // Regenerate session to prevent session fixation attacks
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user
        ], 201);
    }
    
    // Login using session-based authentication
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            // Alternatively, you can throw a ValidationException here.
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Regenerate the session to prevent session fixation
        $request->session()->regenerate();

        // Now the user is authenticated via session cookies.
        return response()->json(['message' => 'Logged in successfully', 'user' => $request->user()], 200);
    }

    // Logout and invalidate the session
    public function logout(Request $request)
    {
        // Log out the user using the web guard
        Auth::logout();

        // Invalidate the session and regenerate the CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();
		// // Revoke the current token
		$request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    // Reset the users password using their email 
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $email = $request->email;
        $new_password = $request->password;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($new_password);
        $user->save();


        return response()->json(['message' => 'Password reset successfully.']);
    }

    // Get the authenticated user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

}
