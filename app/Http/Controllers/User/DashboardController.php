<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'pending_bookings' => $user->bookings()->where('status', 'pending')->count(),
            'confirmed_bookings' => $user->bookings()->where('status', 'confirmed')->count(),
            'total_spent' => Payment::whereHas('booking', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('payment_status', 'completed')->sum('amount'),
            'this_month_spent' => Payment::whereHas('booking', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('payment_status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->sum('amount'),
        ];

        $recentBookings = $user->bookings()
            ->with(['room', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        $upcomingBookings = $user->bookings()
            ->with(['room'])
            ->where('status', 'confirmed')
            ->where('check_in_date', '>=', now())
            ->orderBy('check_in_date')
            ->take(3)
            ->get();

        return view('user.dashboard', compact('stats', 'recentBookings', 'upcomingBookings'));
    }
}

