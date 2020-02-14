<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAgreementRequest extends Mailable
{
    use Queueable, SerializesModels;

    protected $messageData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($messageData, $agreementRequest)
    {
        $this->messageData = $messageData;
        $this->messageData['path'] = "cabinet/index#/accountant/new_agreements/request/{$agreementRequest->id}";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->view('emails.agreements.new_request_to_accountant')->with([
            'messageData' => $this->messageData
        ]);
    }
}
