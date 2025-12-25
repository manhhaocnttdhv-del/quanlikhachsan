<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'shift_date',
        'shift_type',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function reports()
    {
        return $this->hasMany(ShiftReport::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->where('shift_date', Carbon::today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('shift_date', '>=', Carbon::today());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeForEmployee($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Kiểm tra xem ca có đang diễn ra không
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = Carbon::now();
        $shiftDateTime = Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->end_time);

        return $now >= $shiftDateTime && $now <= $endDateTime;
    }

    /**
     * Lấy tên ca bằng tiếng Việt
     */
    public function getShiftTypeName(): string
    {
        return match($this->shift_type) {
            'morning' => 'Ca sáng',
            'afternoon' => 'Ca trưa',
            'evening' => 'Ca tối',
            default => 'Chưa xác định',
        };
    }

    /**
     * Lấy giờ bắt đầu và kết thúc dựa trên shift_type
     */
    public static function getShiftTimes($shiftType): array
    {
        return match($shiftType) {
            'morning' => ['start_time' => '06:00', 'end_time' => '12:00'],
            'afternoon' => ['start_time' => '12:00', 'end_time' => '18:00'],
            'evening' => ['start_time' => '18:00', 'end_time' => '24:00'],
            default => ['start_time' => '08:00', 'end_time' => '17:00'],
        };
    }

    /**
     * Tính doanh thu từ các payment completed trong ca này
     */
    public function calculateRevenue()
    {
        $shiftStart = Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->start_time);
        $shiftEnd = Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->end_time);

        // Lấy các payment completed trong khoảng thời gian ca
        $payments = \App\Models\Payment::where('payment_status', 'completed')
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$shiftStart, $shiftEnd])
            ->with('booking')
            ->get();

        // Lọc các payment có booking checkout trong ngày ca
        $payments = $payments->filter(function($payment) {
            return $payment->booking && 
                   $payment->booking->check_out_date->format('Y-m-d') === $this->shift_date->format('Y-m-d');
        });

        $revenue = [
            'total_revenue' => 0,
            'cash_amount' => 0,
            'card_amount' => 0,
            'transfer_amount' => 0,
            'other_amount' => 0,
            'total_checkouts' => 0,
            'paid_checkouts' => 0,
            'unpaid_checkouts' => 0,
        ];

        foreach ($payments as $payment) {
            $revenue['total_revenue'] += $payment->amount;
            
            if ($payment->payment_method === 'cash') {
                $revenue['cash_amount'] += $payment->amount;
            } elseif (in_array($payment->payment_method, ['credit_card', 'debit_card'])) {
                $revenue['card_amount'] += $payment->amount;
            } elseif (in_array($payment->payment_method, ['bank_transfer', 'bank_transfer_qr', 'vnpay', 'momo'])) {
                $revenue['transfer_amount'] += $payment->amount;
            } else {
                $revenue['other_amount'] += $payment->amount;
            }
        }

        // Đếm số checkout trong ngày ca
        $checkouts = \App\Models\Booking::where('check_out_date', $this->shift_date)
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->get();

        $revenue['total_checkouts'] = $checkouts->count();
        $revenue['paid_checkouts'] = $checkouts->filter(function($booking) {
            return $booking->payment && $booking->payment->payment_status === 'completed';
        })->count();
        $revenue['unpaid_checkouts'] = $revenue['total_checkouts'] - $revenue['paid_checkouts'];

        return $revenue;
    }
}
