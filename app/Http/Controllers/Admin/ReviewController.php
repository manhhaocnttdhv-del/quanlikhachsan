<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Review::with(['user', 'room', 'booking']);

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo tên user hoặc email
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tìm kiếm theo tên phòng
        if ($request->has('room_search')) {
            $roomSearch = $request->room_search;
            $query->whereHas('room', function($q) use ($roomSearch) {
                $q->where('room_number', 'like', "%{$roomSearch}%")
                  ->orWhere('room_type', 'like', "%{$roomSearch}%");
            });
        }

        $reviews = $query->latest()->paginate(15);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function show($id)
    {
        $review = Review::with(['user', 'room', 'booking'])->findOrFail($id);
        return view('admin.reviews.show', compact('review'));
    }

    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['status' => 'approved']);

        return back()->with('success', 'Đã duyệt đánh giá thành công!');
    }

    public function reject($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['status' => 'rejected']);

        return back()->with('success', 'Đã từ chối đánh giá!');
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Xóa đánh giá thành công!');
    }
}
