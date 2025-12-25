<?php

namespace App\Services;

use App\Models\Booking;
use App\Mail\BookingNotificationMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EmployeeNotificationService
{
    /**
     * Lấy danh sách booking sắp check-in hôm nay
     */
    public function getUpcomingCheckIns()
    {
        $today = Carbon::today();
        
        return Booking::where('check_in_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['user', 'room'])
            ->orderBy('check_in_time', 'asc')
            ->get();
    }

    /**
     * Lấy danh sách booking sắp check-out hôm nay
     */
    public function getUpcomingCheckOuts()
    {
        $today = Carbon::today();
        
        return Booking::where('check_out_date', $today)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->with(['user', 'room', 'payment'])
            ->orderBy('check_out_time', 'asc')
            ->get();
    }

    /**
     * Lấy danh sách booking chưa thanh toán
     */
    public function getUnpaidBookings()
    {
        return Booking::whereIn('status', ['confirmed', 'checked_in'])
            ->where(function($query) {
                $query->whereDoesntHave('payment')
                      ->orWhereHas('payment', function($q) {
                          $q->where('payment_status', '!=', 'completed');
                      });
            })
            ->where('check_out_date', '>=', Carbon::today())
            ->with(['user', 'room', 'payment'])
            ->orderBy('check_out_date', 'asc')
            ->take(10)
            ->get();
    }

    /**
     * Lấy tất cả thông báo cho nhân viên
     */
    public function getAllNotifications()
    {
        return [
            'upcoming_check_ins' => $this->getUpcomingCheckIns(),
            'upcoming_check_outs' => $this->getUpcomingCheckOuts(),
            'unpaid_bookings' => $this->getUnpaidBookings(),
        ];
    }

    /**
     * Gửi email thông báo cho khách hàng
     */
    public function sendEmailNotification(Booking $booking, string $type)
    {
        try {
            Mail::to($booking->user->email)->send(new \App\Mail\BookingNotificationMail($booking, $type));
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send email notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi email cho tất cả booking sắp check-in hôm nay
     */
    public function sendCheckInReminders()
    {
        $bookings = $this->getUpcomingCheckIns();
        $sent = 0;
        
        foreach ($bookings as $booking) {
            if ($this->sendEmailNotification($booking, 'check_in_reminder')) {
                $sent++;
            }
        }
        
        return $sent;
    }

    /**
     * Gửi email cho tất cả booking sắp check-out hôm nay
     */
    public function sendCheckOutReminders()
    {
        $bookings = $this->getUpcomingCheckOuts();
        $sent = 0;
        
        foreach ($bookings as $booking) {
            if ($this->sendEmailNotification($booking, 'check_out_reminder')) {
                $sent++;
            }
        }
        
        return $sent;
    }

    /**
     * Gửi email cho booking chưa thanh toán
     */
    public function sendUnpaidReminders()
    {
        $bookings = $this->getUnpaidBookings();
        $sent = 0;
        
        foreach ($bookings as $booking) {
            if ($this->sendEmailNotification($booking, 'unpaid_reminder')) {
                $sent++;
            }
        }
        
        return $sent;
    }
}

