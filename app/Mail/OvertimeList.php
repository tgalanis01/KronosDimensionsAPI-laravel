<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class OvertimeList
 * @package App\Mail
 */
class OvertimeList extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var
     */
    protected $excelFilePath;

    /**
     * @var
     */
    protected $pdfFilePath;

    /**
     * @var
     */
    public $bodyMessage;

    /**
     * @var
     */
    public $subject;

    /**
     * OvertimeList constructor.
     * @param $excelFilePath
     * @param $pdfFilePath
     * @param $subject
     * @param $bodyMessage
     */
    public function __construct($excelFilePath, $pdfFilePath, $subject, $bodyMessage)
    {
        $this->excelFilePath = $excelFilePath;
        $this->pdfFilePath = $pdfFilePath;

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

        return $this->view('emails.overtimelist')
            ->with([
                'bodyMessage' => $this->bodyMessage
            ])
            ->attachFromStorage($this->excelFilePath)
            ->attachFromStorage($this->pdfFilePath)
            ->subject($this->subject);

    }
}
