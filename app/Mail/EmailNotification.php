<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $appointment;
    public $formattedDate;
    public $availability;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $appointment, $formattedDate, $availability)
    {
        $this->subject = $subject;
        $this->appointment = $appointment;
        $this->formattedDate = $formattedDate;
        $this->availability = $availability;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.emailnotification')
            ->subject($this->subject);
    }
}

