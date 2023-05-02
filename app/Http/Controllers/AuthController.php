<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Morilog\Jalali\Jalalian;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->except('login', 'register');
    }

    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'phone' => 'required|numeric|digits_between:10,12',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }

        $credentials = $request->only('phone', 'password');
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        ]);

    }

    public function register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'sex' => 'required|in:male,female|max:255',
            'birthdate' => 'required|date|max:255',
            'blood_type' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:9,12|unique:users',
            'email' => 'string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }
        $user = User::create(
            $request->only('firstname', 'lastname', 'sex', 'blood_type', 'phone', 'email')
            + ['birthdate' => Jalalian::fromFormat('Y-m-d', $request->birthdate)->toCarbon()]
            + ['password' => Hash::make($request->password)]
        );

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}
