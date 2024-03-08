<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Availability;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Appointment;

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

    public function clientList()
    {

        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'doctor') {

                $doctor = $user->id;

                $appointments = Appointment::with(['availability' => function ($query) {
                    $query->select('_id', 'availableDate', 'startTime', 'endTime');
                }])
                    ->whereHas('availability', function ($query) use ($doctor) {
                        $query->where('doctor_id', $doctor);
                    })
                    ->get();

                return response()->json([
                    'message' => 'Appointments listed',
                    'data' => $appointments,
                ]);
            } else {
                // If the authenticated user is not a doctor, return an error response
                return response()->json([
                    'message' => 'Unauthorized. Only doctors can view appointment records.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error displaying appointment record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function statusAccept($id)
    {
        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'doctor') {
                $appointment = Appointment::findOrFail($id);
                $appointment->update([
                    'appoint_status' => 'Accepted',
                ]);

                return response()->json([
                    'message' => 'Accepted',
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only doctor can use this action',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error accepting client appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function statusReject($id)
    {
        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'doctor') {
                $appointment = Appointment::findOrFail($id);
                $appointment->update([
                    'appoint_status' => 'Rejected',
                ]);

                return response()->json([
                    'message' => 'Rejected',
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only doctor can use this action',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error rejecting client appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function clientListCalender()
    {

        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'doctor') {
                $doctor = $user->id;
                $appointments = Appointment::where('appoint_status', 'Accepted')
                    ->whereHas('availability', function ($query) use ($doctor) {
                        $query->where('doctor_id', $doctor);
                    })
                    ->get();

                $events = [];
                foreach ($appointments as $appointment) {
                    $events[] = [
                        'title' => $appointment->appoint_name,
                        'start' => $appointment->availability['availableDate'] . ' ' . $appointment->availability['startTime'],
                        'end' => $appointment->availability['availableDate'] . ' ' . $appointment->availability['endTime'],
                        'id' => $appointment->_id,
                    ];
                }

                return response()->json($events);
            } else {
                // If the authenticated user is not a doctor, return an error response
                return response()->json([
                    'message' => 'Unauthorized. Only doctors can view appointment records.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error displaying appointment record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function statusComplete($id)
    {
        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'doctor') {
                $appointment = Appointment::findOrFail($id);
                $appointment->update([
                    'appoint_status' => 'Completed',
                ]);

                return response()->json([
                    'message' => 'Completed',
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only doctor can use this action',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error completing client appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
