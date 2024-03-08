<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Availability;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:customer');
    }

    public function dashboard()
    {
        return response()->json(['message' => 'Customer Dashboard']);
    }


    public function doctorAvailableList()
    {
        $doctors = User::where('role', 'doctor')->whereNotIn('doctorStatus', ['Pending', 'Rejected'])->get();

        $doctorSchedule = [];

        foreach ($doctors as $doctor) {
            $availabilities = Availability::where('doctor_id', $doctor->id)->get();

            if ($availabilities->isEmpty()) {
                // If no availabilities, set availability to "Unavailable"
                $doctorSchedule[] = [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialization' => $doctor->specialization,
                    'location' => $doctor->location,
                    'availability' => 'Unavailable',
                ];
            } else {
                $fullyBooked = true;

                foreach ($availabilities as $availability) {
                    $appointment = Appointment::where('available_id', $availability->id)->first();

                    if (!$appointment) {
                        // If any availability is not booked, set fullyBooked to false
                        $fullyBooked = false;
                        break;
                    }
                }

                if ($fullyBooked) {
                    $doctorSchedule[] = [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'specialization' => $doctor->specialization,
                        'location' => $doctor->location,
                        'availability' => 'Fully Booked',
                    ];
                } else {
                    $doctorSchedule[] = [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'specialization' => $doctor->specialization,
                        'location' => $doctor->location,
                        'availability' => 'Available',
                    ];
                }
            }
        }

        return response()->json([
            'message' => 'Doctor Schedule Listed',
            'data' => $doctorSchedule,
        ]);
    }

    public function doctorBookingProfile($id)
    {
        try {

            $doctor = User::where('_id', $id)->where('role', 'doctor')->first();

            if (!$doctor) {
                return response()->json(['message' => 'Doctor not found'], 404);
            }

            $availability = Availability::where('doctor_id', $id)->get();

            return response()->json([
                'message' => 'Doctor details and availability',
                'data' => [
                    'doctor' => $doctor,
                    'availability' => $availability,
                ],

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting availability record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function appointmentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:50|string',
            'email' => 'required|email|string',
            'time' => 'required|string',
            //'availableId' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->toJson(),
                'message' => 'Validation failed',
            ], 422);
        }

        $user = JWTAuth::user();

        try {
            if ($user && $user->role === 'customer') {
                $appointment = Appointment::create([
                    'customer_id' => $user->id,
                    'available_id' => $request->input('time'),
                    'appoint_name' => $request->input('name'),
                    'appoint_email' => $request->input('email'),
                    'appoint_status' => 'Pending',
                ]);

                return response()->json([
                    'message' => 'Appointment successfully added',
                    'user' => $appointment,
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only customers can make appointment.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding appointment record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function appointmentList()
    {
        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'customer') {
                $appointment = Appointment::all();
                return response()->json([
                    'message' => 'Appointment Listed',
                    'data' => $appointment,
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only customers can view appointment records.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error displaying appointment record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
