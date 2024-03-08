<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['doctorRegister', 'customerRegister', 'login']]);
    }

    public function doctorRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorname' => 'required|string|min:4|max:50',
            'doctoremail' => 'required|string|email|unique:users,email',
            'doctorpassword' => 'required|string|min:5|regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*]).*$/',
            'doctormobile' => 'required|string|max:11',
            'specialization' => 'required|string',
            'location' => 'required|string',
            'description' => 'required|string',
        ], [
            'email.unique' => 'Email is already taken',
        ]);

        if ($validator->fails()) {
            // Return a JSON response with validation errors and status code 422
            return response()->json([
                'errors' => $validator->errors()->toJson(),
                'message' => 'Validation failed',
            ], 422);
        }

        $user = User::create([
            'name' => $request->input('doctorname'),
            'email' => $request->input('doctoremail'),
            'password' => Hash::make($request->input('doctorpassword')),
            'mobile' => $request->input('doctormobile'),
            'specialization' => $request->input('specialization'),
            'location' => $request->input('location'),
            'description' => $request->input('description'),
            'role' => 'doctor',
            'doctorStatus' => 'Pending'
        ]);

        return response()->json([
            'message' => 'Doctor successfully registered',
            'user' => $user,
        ], 201);
    }

    public function customerRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|max:50',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:4|regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*]).*$/',
            'number' => 'required|string|max:11',
        ], [
            'email.unique' => 'Email is already taken',
        ]);

        if ($validator->fails()) {
            // Return a JSON response with validation errors and status code 422
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'mobile' => $request->input('number'),
            'specialization' => 'none',
            'location' => 'none',
            'description' => 'none',
            'role' => 'customer',
            'doctorStatus' => 'none',
        ]);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,

        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:5|max:50',
        ]);

        if ($validator->fails()) {
            // Return a JSON response with validation errors and status code 422
            return response()->json([
                'errors' => $validator->errors()->toJson(),
                'message' => 'Validation failed',
            ], 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Invalid password or email'], 401);
        }

        // Check the doctor's status before generating the token
        $user = auth()->user();
        if ($user->role === 'doctor' && $user->doctorStatus === 'Pending') {
            // If doctor status is 'Pending', return an error response
            return response()->json(['error' => 'Doctor registration is pending approval'], 401);
        } else if ($user->role === 'doctor' && $user->doctorStatus === 'Rejected') {
            // If doctor status is 'Pending', return an error response
            return response()->json(['error' => 'Doctor registration is rejected'], 401);
        }

        return $this->createNewToken($token);
    }

    public function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => auth()->user() // Use to get the authenticated user
        ]);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'User logged out',
        ], 201);
    }
}
