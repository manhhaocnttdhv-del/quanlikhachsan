<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::available();

        // Lọc theo loại phòng
        if ($request->has('room_type') && $request->room_type != '') {
            $query->where('room_type', $request->room_type);
        }

        // Lọc theo giá
        if ($request->has('min_price')) {
            $query->where('price_per_night', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        // Lọc theo số người
        if ($request->has('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        $rooms = $query->with('images')->paginate(12);

        return view('user.rooms.index', compact('rooms'));
    }

    public function show($id)
    {
        $room = Room::with(['images', 'approvedReviews.user'])->findOrFail($id);
        return view('user.rooms.show', compact('room'));
    }
}
