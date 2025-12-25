<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Kiểm tra xem admin có phải là admin không
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra xem admin có phải là manager không
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Kiểm tra xem admin có phải là employee không
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Kiểm tra xem admin có quyền truy cập chức năng không
     */
    public function canAccess(string $permission): bool
    {
        // Admin có toàn quyền
        if ($this->isAdmin()) {
            return true;
        }

        // Manager chỉ có quyền hạn chế
        $managerPermissions = [
            'view_dashboard',
            'manage_rooms',
            'manage_bookings',
            'manage_payments',
            'manage_customers',
            'manage_reviews',
        ];

        return in_array($permission, $managerPermissions);
    }

    /**
     * Quan hệ với shifts (ca làm việc)
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * Lấy ca làm việc hôm nay
     */
    public function todayShift()
    {
        return $this->shifts()->today()->first();
    }

    /**
     * Lấy ca làm việc đang active
     */
    public function activeShift()
    {
        return $this->shifts()->active()->first();
    }

    /**
     * Quan hệ với shift reports (báo cáo ca)
     */
    public function shiftReports()
    {
        return $this->hasMany(ShiftReport::class);
    }
}
