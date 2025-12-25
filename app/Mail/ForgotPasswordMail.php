<?php

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class ForgotPasswordMail extends Mailable
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function build()
    {
        return $this->subject('Đặt lại mật khẩu')->view('emails.reset_password')->with([
            'token' => $this->token,
        ]);
    }
}

