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

    public function customerCalendar()
    {
        try {
            $user = JWTAuth::user();
            if ($user && $user->role === 'customer') {
                $customer = $user->id;
                $appointments = Appointment::where('appoint_status', 'Accepted')
                    ->where('customer_id', $customer)
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
}
