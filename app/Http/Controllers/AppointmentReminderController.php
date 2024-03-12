<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\EmailNotification;

class AppointmentReminderController extends Controller
{
    public function emailNotification(Request $request)
    {

        // Get all accepted appointments
        $appointments = Appointment::with(['customer', 'availability'])
            ->where('appoint_status', 'Accepted')
            ->get();

        foreach ($appointments as $appointment) {

            // Check if email notification is enabled for the customer
            if ($appointment->customer->emailnotification === true) {
                $availability = $appointment->availability;
                $formattedDate = Carbon::parse($availability->availableDate)->isoFormat('Do MMMM YYYY');

                $reminderDate = Carbon::parse($availability->availableDate)->subDay();
        
                // Send email reminder using mail:raw
                $customer = $appointment->appoint_email;
                $subject = "MedPoint Appointment Reminder";
              

                Mail::to($customer)
                    ->later($reminderDate, new EmailNotification($subject, $appointment, $formattedDate, $availability));
            } else {
                return response()->json(['message' => 'Appointment email reminders turned off']);
            }
        }

        return response()->json(['message' => 'Appointment reminders sent successfully']);
    }

    public function updateNotificationSettings(Request $request)
    {
        $user = auth()->user();

        User::where('_id', $user->_id)
            ->update([
                'emailnotification' => $request->input('emailnotification'),
                'smsnotification' => $request->input('smsnotification'),
                'appnotification' => $request->input('appnotification'),
            ]);



        return response()->json(['message' => 'Notification settings updated successfully']);
    }
}
