<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Availability;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{

    //store availability (doctor)
    public function availabilityStore(Request $request)
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

        try {
            // Check if the user is a doctor (you can customize this check based on your user roles)
            if ($user && $user->role === 'doctor') {

                $existingAvailable = Availability::where('doctor_id', $user->id)
                    ->where('availableDate', $request->input('availableDate'))
                    ->where(function ($query) use ($request) {
                        $query->where(function ($query) use ($request) {
                            $query->where('startTime', '<', $request->input('startTime'))
                                ->where('endTime', '>', $request->input('startTime'));
                        })->orWhere(function ($query) use ($request) {
                            $query->where('startTime', '>=', $request->input('startTime'))
                                ->where('startTime', '<', $request->input('endTime'));
                        });
                    })
                    ->exists();

                //duplicate schedule
                if ($existingAvailable) {
                    return response()->json([
                        'message' => 'Duplicate availability. This schedule already exists.',
                    ], 422);
                }


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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding availability record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //display available list (Doctor)
    public function availableList()
    {

        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'doctor') {
                $doctor = $user->id;
                $available = Availability::where('doctor_id', $doctor)->get();
                return response()->json([
                    'message' => 'Schedule Listed',
                    'data' => $available,
                ]);
            } else {
                // If the authenticated user is not a doctor, return an error response
                return response()->json([
                    'message' => 'Unauthorized. Only doctors can view availability records.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error displaying availability record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //delete available (doctor)
    public function availableDelete($id)
    {
        try {
            $user = JWTAuth::user();

            if ($user && $user->role === 'doctor') {
                $available = Availability::where('_id', $id)
                    ->where('doctor_id', $user->id)
                    ->first();

                if (!$available) {
                    return response()->json([
                        'message' => 'Availability not found or unauthorized to delete',
                    ], 404);
                }

                $available->delete();

                return response()->json([
                    'message' => 'Availability successfully deleted',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting availability record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //display doctor available list (customer)
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

    //display doctor profile (customer) after customer press check availability
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
}
