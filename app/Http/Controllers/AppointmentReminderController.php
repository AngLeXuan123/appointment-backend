<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\EmailNotification;
use Twilio\Rest\Client;
use Nexmo\Client\Credentials\Basic;




class AppointmentReminderController extends Controller
{
    public function emailNotification(Request $request)
    {
        $now = Carbon::now();
        // Get all accepted appointments
        $appointments = Appointment::with(['customer', 'availability'])
            ->where('appoint_status', 'Accepted')
            ->whereHas('availability', function ($query) use ($now) {
                $query->where('availableDate', '>', $now->toDateString());
            })
            ->whereHas('customer', function ($query) {
                $query->where('role', 'customer');
            })
            ->get();

        foreach ($appointments as $appointment) {

            // Check if email notification is enabled for the customer
            if (
                $appointment->customer->emailnotification === true
                && $appointment->customer->smsnotification === true
                && $appointment->customer->appnotification === true
            ) {
                if ($appointment->reminder_sent === false) {
                    $availability = $appointment->availability;
                    $formattedDate = Carbon::parse($availability->availableDate)->isoFormat('Do MMMM YYYY');

                    $reminderDate = Carbon::parse($availability->availableDate)->subDay();

                    $customer = $appointment->appoint_email;
                    $subject = "MedPoint Appointment Reminder";

                    $appointment->update(['reminder_sent' => true]);

                    $sid = getenv("TWILIO_SID");
                    $token = getenv("TWILIO_TOKEN");
                    $sender = getenv("TWILIO_PHONE");
                    $twilio = new Client($sid, $token);

                    $message = $twilio->messages
                        ->create(
                            "+6" . $appointment->customer->mobile, // to
                            [
                                "body" => "Dear $appointment->appoint_name, \nYour appointment is on $formattedDate, \nFrom: $availability->startTime, \nTo: $availability->endTime",
                                "from" => $sender,
                            ]
                        );

                    Mail::to($customer)
                        ->later($reminderDate, new EmailNotification($subject, $appointment, $formattedDate, $availability));
                }
            }

            if ($appointment->customer->emailnotification === true) {
                if ($appointment->reminder_sent === false) {
                    $availability = $appointment->availability;
                    $formattedDate = Carbon::parse($availability->availableDate)->isoFormat('Do MMMM YYYY');

                    $reminderDate = Carbon::parse($availability->availableDate)->subDay();

                    $customer = $appointment->appoint_email;
                    $subject = "MedPoint Appointment Reminder";

                    $appointment->update(['reminder_sent' => true]);

                    Mail::to($customer)
                        ->later($reminderDate, new EmailNotification($subject, $appointment, $formattedDate, $availability));
                }
            }

            if ($appointment->customer->smsnotification === true) {
                if ($appointment->reminder_sent === false) {

                    //twilio
                    $sid = getenv("TWILIO_SID");
                    $token = getenv("TWILIO_TOKEN");
                    $sender = getenv("TWILIO_PHONE");
                    $twilio = new Client($sid, $token);


                    $availability = $appointment->availability;
                    $formattedDate = Carbon::parse($availability->availableDate)->isoFormat('Do MMMM YYYY');
                    //$reminderDate = Carbon::parse($availability->availableDate)->subDay();

                    $appointment->update(['reminder_sent' => true]);

                    $message = $twilio->messages
                        ->create(
                            "+6" . $appointment->customer->mobile, // to
                            [
                                "body" => "Dear $appointment->appoint_name, \nYour appointment is on $formattedDate, \nFrom: $availability->startTime, \nTo: $availability->endTime",
                                "from" => $sender,
                            ]
                        );
                }
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
