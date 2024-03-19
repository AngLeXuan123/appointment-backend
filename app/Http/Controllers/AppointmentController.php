<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Availability;
use App\Models\User;
use App\Mail\EmailNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    //store appointment (customer)
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
                    'appoint_status' => "Accepted",
                    'reminder_sent' => false,
                ]);

                $now = Carbon::now();
                
                $appointments = Appointment::with(['customer', 'availability'])
                    ->where('_id', $appointment->id)
                    ->where('appoint_status', 'Accepted')
                    ->whereHas('availability', function ($query) use ($now) {
                        $query->where('availableDate', '>', $now->toDateString());
                    })
                    ->whereHas('customer', function ($query) {
                        $query->where('role', 'customer');
                    })
                    ->get();

                foreach ($appointments as $appointment) {

                    if (
                        $appointment->customer->emailnotification === true
                        || $appointment->customer->smsnotification === true
                        || $appointment->customer->appnotification === true
                    ) {

                        if ($appointment->reminder_sent === false) {
                            $availability = $appointment->availability;
                            $formattedDate = Carbon::parse($availability->availableDate)->isoFormat('Do MMMM YYYY');

                            $reminderDate = Carbon::parse($availability->availableDate)->subDay();

                            $customer = $appointment->appoint_email;
                            $subject = "MedPoint Appointment Reminder";

                            $appointment->update(['reminder_sent' => true]);

                            Mail::to($customer)
                                ->later($reminderDate, new EmailNotification($subject, $appointment, $formattedDate, $availability));
                        } else {
                            return response()->json(['message' => 'reminder has been sent once']);
                        }
                    } else {
                        return response()->json(['message' => 'Appointment email reminders turned off']);
                    }
                }

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


    //display appointment list in doctor client list tab (doctor)
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

    //display appointment list (customer)
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
