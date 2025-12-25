<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ShiftReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'admin_id',
        'report_date',
        'total_revenue',
        'cash_amount',
        'card_amount',
        'transfer_amount',
        'other_amount',
        'total_checkouts',
        'paid_checkouts',
        'unpaid_checkouts',
        'notes',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_revenue' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'card_amount' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
        'other_amount' => 'decimal:2',
    ];

    // Relationships
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('report_date', $date);
    }

    public function scopeForEmployee($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }
}
