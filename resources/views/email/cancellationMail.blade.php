<!-- resources/views/appointmentReminder.blade.php -->

<html>
<head>
    <title>MedPoint Appointment Reminder</title>
</head>
<body>
    <p>Dear {{ $appointment->appoint_name }},</p>
    <p>Your appointment is cancelled on {{ $formattedDate }} due to certain reason,</p>
    <p>From: {{ $availability->startTime }},</p>
    <p>To: {{ $availability->endTime }}</p>
    <p>Sorry for the inconvinient</p>

    <!-- You can customize the email content further based on your needs -->
</body>
</html>
