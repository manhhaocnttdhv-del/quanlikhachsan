<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Request $request)
    {
        $roomId = $request->room_id;
        $bookingId = $request->input('booking_id');

        $room = Room::findOrFail($roomId);
        
        // Kiểm tra nếu có booking_id, đảm bảo booking thuộc về user
        $booking = null;
        if ($bookingId) {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', Auth::id())
                ->where('status', 'checked_out')
                ->firstOrFail();
        }

        // Kiểm tra xem user đã đánh giá phòng này chưa
        $existingReview = Review::where('user_id', Auth::id())
            ->where('room_id', $roomId)
            ->first();

        if ($existingReview) {
            return redirect()->route('rooms.show', $roomId)
                ->with('error', 'Bạn đã đánh giá phòng này rồi.');
        }

        return view('user.reviews.create', compact('room', 'booking'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        // Kiểm tra nếu có booking_id, đảm bảo booking thuộc về user
        if (isset($validated['booking_id']) && $validated['booking_id']) {
            $booking = Booking::where('id', $validated['booking_id'])
                ->where('user_id', Auth::id())
                ->firstOrFail();
        }

        // Kiểm tra xem user đã đánh giá phòng này chưa
        $existingReview = Review::where('user_id', Auth::id())
            ->where('room_id', $validated['room_id'])
            ->first();

        if ($existingReview) {
            return back()->with('error', 'Bạn đã đánh giá phòng này rồi.');
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'room_id' => $validated['room_id'],
            'booking_id' => isset($validated['booking_id']) ? $validated['booking_id'] : null,
            'rating' => $validated['rating'],
            'comment' => isset($validated['comment']) ? $validated['comment'] : null,
            'status' => 'pending', // Chờ admin duyệt
        ]);

        return redirect()->route('rooms.show', $validated['room_id'])
            ->with('success', 'Cảm ơn bạn đã đánh giá! Đánh giá của bạn đang chờ được duyệt.');
    }

    public function index()
    {
        $reviews = Auth::user()->reviews()
            ->with(['room', 'booking'])
            ->latest()
            ->paginate(10);

        return view('user.reviews.index', compact('reviews'));
    }
}
