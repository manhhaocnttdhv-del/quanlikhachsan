<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'shift_id',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'number_of_guests',
        'total_price',
        'status',
        'special_requests',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'check_in_time' => 'string', // Time field, keep as string
        'check_out_time' => 'string', // Time field, keep as string
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payment()
    {
        // Payment chính (đầu tiên) - để tương thích với code cũ
        return $this->hasOne(Payment::class)->oldest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function additionalCharges()
    {
        return $this->hasMany(BookingAdditionalCharge::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'checked_in']);
    }

    /**
     * Kiểm tra xem có booking nào trùng lặp với khoảng thời gian này không
     * Logic: Hai khoảng thời gian overlap nếu:
     * - check_in_date mới < check_out_date cũ VÀ check_out_date mới > check_in_date cũ
     */
    public function scopeOverlapping($query, $roomId, $checkInDate, $checkOutDate, $excludeId = null)
    {
        return $query->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function($q) use ($checkInDate, $checkOutDate) {
                // Kiểm tra overlap: booking mới bắt đầu trước khi booking cũ kết thúc
                // VÀ booking mới kết thúc sau khi booking cũ bắt đầu
                $q->where('check_in_date', '<', $checkOutDate)
                  ->where('check_out_date', '>', $checkInDate);
            })
            ->when($excludeId, function($q) use ($excludeId) {
                $q->where('id', '!=', $excludeId);
            });
    }
}
