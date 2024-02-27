<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeTimeOff extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var
     */
    public $existingEvents;

    /**
     * @var
     */
    public $newEvents;

    /**
     * @var
     */
    public $bodyMessage;

    /**
     * @var
     */
    public $subject;

    /**
     * Create a new message instance.
     *
     * @param $existingEvents
     * @param $newEvents
     * @param $subject
     * @param $bodyMessage
     */
    public function __construct($existingEvents, $newEvents, $subject, $bodyMessage)
    {
        $this->existingEvents = $existingEvents;
        $this->newEvents = $newEvents;


        $this->subject = $subject;
        $this->bodyMessage = $bodyMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.employeetimeoff')
            ->with([
                'existingEvents' => $this->existingEvents,
                'newEvents' => $this->newEvents,
                'bodyMessage' => $this->bodyMessage
            ])
            ->subject($this->subject);
    }
}
