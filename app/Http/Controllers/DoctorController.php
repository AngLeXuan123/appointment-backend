<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Availability;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Appointment;
use App\Mail\AppointmentCancelMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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

    public function statusCancel($id)
    {
        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'doctor') {
                $appointment = Appointment::findOrFail($id);
                $appointment->update([
                    'appoint_status' => 'Cancelled',
                ]);

                //send notification for cancelled appointment
                $now = Carbon::now();
                $appointments = Appointment::with(['customer', 'availability'])
                    ->where('_id', $id)
                    ->where('appoint_status', 'Cancelled')
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

                        $availability = $appointment->availability;
                        $formattedDate = Carbon::parse($availability->availableDate)->isoFormat('Do MMMM YYYY');

                        $reminderDate = Carbon::parse($availability->availableDate)->subDay();

                        $customer = $appointment->appoint_email;
                        $subject = "MedPoint Appointment Cancellation";

                        Mail::to($customer)
                            ->later($reminderDate, new AppointmentCancelMail($subject, $appointment, $formattedDate, $availability));
                    } else {
                        return response()->json(['message' => 'Appointment email reminders turned off']);
                    }
                }

                return response()->json([
                    'message' => 'Cancelled',
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

    public function doctorCalendar()
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
