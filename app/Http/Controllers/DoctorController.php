<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Availability;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class DoctorController extends Controller
{

    function __construct()
    {
        $this->middleware(['role:doctor']);
    }

    public function dashboard()
    {
        return response()->json(['message' => 'Doctor Dashboard']);
    }

    public function availability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'availableDate' => 'required',
            'startTime' => 'required',
            'endTime' => 'required',
        ]);
        if ($validator->fails()) {
            // Return a JSON response with validation errors and status code 422
            return response()->json([
                'errors' => $validator->errors()->toJson(),
                'message' => 'Validation failed',
            ], 422);
        }

        $user = JWTAuth::user();

        // Check if the user is a doctor (you can customize this check based on your user roles)
        if ($user && $user->role === 'doctor') {
            // Create the availability record with the doctor_id
            $available = Availability::create([
                'doctor_id' => $user->id,
                'availableDate' => $request->input('availableDate'),
                'startTime' => $request->input('startTime'),
                'endTime' => $request->input('endTime'),
            ]);

            return response()->json([
                'message' => 'Schedule successfully added',
                'user' => $available,
            ], 201);
        } else {
            // If the authenticated user is not a doctor, return an error response
            return response()->json([
                'message' => 'Unauthorized. Only doctors can add availability.',
            ], 403);
        }
    }

    public function availableList()
    {
        $available = Availability::all();
        return response()->json([
            'message' => 'Schedule Listed',
            'data' => $available,
        ]);
    }
}
