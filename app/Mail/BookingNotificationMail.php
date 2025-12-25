<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $type; // 'check_in_reminder', 'check_out_reminder', 'unpaid_reminder'

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $type)
    {
        $this->booking = $booking;
        $this->type = $type;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = match($this->type) {
            'check_in_reminder' => 'Nhắc nhở: Check-in hôm nay - Booking #' . $this->booking->id,
            'check_out_reminder' => 'Nhắc nhở: Check-out hôm nay - Booking #' . $this->booking->id,
            'unpaid_reminder' => 'Nhắc nhở: Booking chưa thanh toán - Booking #' . $this->booking->id,
            default => 'Thông báo về booking của bạn',
        };

        return $this->subject($subject)
                    ->view('emails.booking-notification')
                    ->with([
                        'booking' => $this->booking,
                        'type' => $this->type,
                    ]);
    }
}

