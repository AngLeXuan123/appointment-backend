<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DoctorAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorname' => 'required|string|min:5|max:50',
            'doctoremail' => 'required|string|email|unique:users,email',
            'doctorpassword' => 'required|string|min:5|max:50|regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*]).*$/',
            'doctormobile' => 'required|string|max:11',
            'specialization' => 'required|string',
            'location' => 'required|string',
            'description' => 'required|string',
        ], [
            'email.unique' => 'Email is already taken',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
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
        ]);

        return response()->json([
            'message' => 'Doctor successfully registered',
            'user' => $user,
        ], 201);
    }
}
