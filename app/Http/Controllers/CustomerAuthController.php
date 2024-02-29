<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CustomerAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5|max:50',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:5|max:50|regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*]).*$/',
            'number' => 'required|string|max:11',
        ], [
            'email.unique' => 'Email is already taken',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
     
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'mobile' => $request->input('number'),
            'specialization' => null,
            'location' => null,
            'description' => null,
            'role' => 'customer',
        ]);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,

        ], 201);
    }
}
