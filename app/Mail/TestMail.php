<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $link;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link, $subject)
    {
        $this->link = $link;
        $this->subject = $subject ? $subject : 'Pdfglue Document';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('mail.test');
    }
}