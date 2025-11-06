<?php

// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(6)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email'=> $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user', // default
        ]);

        // Optional: auto-login after register
        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'message' => 'registered',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    public function login(Request $r)
    {
        $r->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        $user = User::where('email', $r->email)->first();
        if (!$user || !Hash::check($r->password, $user->password)) {
            return response()->json(['message'=>'Invalid credentials'], 422);
        }

        // Revoke previous tokens if you want single-session
        // $user->tokens()->delete();

        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'message'=>'logged_in',
            'token'=>$token,
            'user'=>$user
        ]);
    }

    public function me(Request $r)
    {
        return $r->user();
    }

    public function logout(Request $r)
    {
        // Delete current token only:
        $r->user()->currentAccessToken()->delete();

        // Or delete all tokens:
        // $r->user()->tokens()->delete();

        return response()->json(['message'=>'logged_out']);
    }
}
