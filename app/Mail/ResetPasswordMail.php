<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $newPassword;

    public function __construct($newPassword)
    {
        $this->newPassword = $newPassword;
    }

    public function build()
    {
        return $this->subject('Mật khẩu mới của bạn')
                    ->view('emails.reset_password')
                    ->with([
                        'password' => $this->newPassword,
                    ]);
    }
}
