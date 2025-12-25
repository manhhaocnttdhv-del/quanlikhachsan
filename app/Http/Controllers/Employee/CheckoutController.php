<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Shift;
use App\Models\BookingAdditionalCharge;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Hiển thị danh sách khách hàng đến checkout
     * Chỉ hiển thị khi nhân viên đang trong ca làm việc
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        // Kiểm tra xem có phải nhân viên không
        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra ca làm việc hôm nay
        $todayShift = $admin->shifts()
            ->where('shift_date', Carbon::today())
            ->whereIn('status', ['scheduled', 'active'])
            ->first();

        // Kiểm tra xem có nhân viên khác đang làm việc không
        $otherActiveShift = Shift::where('shift_date', Carbon::today())
            ->where('status', 'active')
            ->where('admin_id', '!=', $admin->id)
            ->with('admin')
            ->first();

        // Nếu không có ca, vẫn cho xem nhưng cảnh báo
        $hasShift = $todayShift !== null;
        $isActiveShift = $hasShift && $todayShift->isActive();
        $hasOtherActiveShift = $otherActiveShift !== null;

        // Lấy danh sách booking
        // Hiển thị booking: pending (đã thanh toán), confirmed, checked_in
        // Booking pending chỉ hiển thị nếu đã có payment (đã thanh toán QR đang chờ xác nhận)
        $query = Booking::with(['user', 'room', 'payment'])
            ->where(function($q) {
                $q->whereIn('status', ['confirmed', 'checked_in'])
                  ->orWhere(function($subQ) {
                      // Hiển thị pending nếu đã có payment (đã thanh toán)
                      $subQ->where('status', 'pending')
                           ->whereHas('payment', function($paymentQ) {
                               $paymentQ->whereIn('payment_status', ['pending', 'completed']);
                           });
                  });
            });
        
        // Filter theo ngày checkout hoặc check-in hôm nay
        // Nếu có parameter 'view_all' = true, hiển thị tất cả
        // Nếu không có filter checkout_date, mặc định hiển thị booking hôm nay (check-in hoặc checkout)
        if ($request->has('view_all') && $request->view_all == '1') {
            // Hiển thị tất cả, không filter theo ngày
        } elseif ($request->has('checkout_date') && $request->checkout_date != '') {
            // Filter theo ngày checkout cụ thể
            $query->where('check_out_date', $request->checkout_date);
        } else {
            // Mặc định: hiển thị booking có check-in hoặc checkout hôm nay
            $today = Carbon::today()->format('Y-m-d');
            $query->where(function($q) use ($today) {
                $q->where('check_in_date', $today)
                  ->orWhere('check_out_date', $today);
            });
        }

        // Lọc theo trạng thái thanh toán
        if ($request->has('payment_status') && $request->payment_status != '') {
            if ($request->payment_status === 'paid') {
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', 'completed');
                });
            } elseif ($request->payment_status === 'unpaid') {
                $query->where(function($q) {
                    $q->whereDoesntHave('payment')
                      ->orWhereHas('payment', function($query) {
                          $query->where('payment_status', '!=', 'completed');
                      });
                });
            }
        }

        // Tìm kiếm
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhereHas('room', function($query) use ($search) {
                    $query->where('room_number', 'like', "%{$search}%");
                });
            });
        }

        $bookings = $query->orderBy('check_out_time')->paginate(15);

        // Thống kê theo filter đã chọn
        $checkoutDate = $request->has('checkout_date') && $request->checkout_date != '' 
            ? $request->checkout_date 
            : null;
        $viewAll = $request->has('view_all') && $request->view_all == '1';
        
        // Thống kê: nếu có filter theo ngày, tính theo ngày đó
        // Nếu view_all = true, tính tất cả
        // Nếu không có filter, tính booking hôm nay (check-in hoặc checkout)
        // Bao gồm cả pending nếu đã có payment
        if ($viewAll) {
            $statsQuery = Booking::where(function($q) {
                $q->whereIn('status', ['confirmed', 'checked_in'])
                  ->orWhere(function($subQ) {
                      // Đếm pending nếu đã có payment
                      $subQ->where('status', 'pending')
                           ->whereHas('payment', function($paymentQ) {
                               $paymentQ->whereIn('payment_status', ['pending', 'completed']);
                           });
                  });
            });
        } elseif ($checkoutDate) {
            $statsQuery = Booking::where('check_out_date', $checkoutDate)
                ->where(function($q) {
                    $q->whereIn('status', ['confirmed', 'checked_in'])
                      ->orWhere(function($subQ) {
                          $subQ->where('status', 'pending')
                               ->whereHas('payment', function($paymentQ) {
                                   $paymentQ->whereIn('payment_status', ['pending', 'completed']);
                               });
                      });
                });
        } else {
            $today = Carbon::today()->format('Y-m-d');
            $statsQuery = Booking::where(function($q) {
                $q->whereIn('status', ['confirmed', 'checked_in'])
                  ->orWhere(function($subQ) {
                      $subQ->where('status', 'pending')
                           ->whereHas('payment', function($paymentQ) {
                               $paymentQ->whereIn('payment_status', ['pending', 'completed']);
                           });
                  });
            })
            ->where(function($q) use ($today) {
                $q->where('check_in_date', $today)
                  ->orWhere('check_out_date', $today);
            });
        }
        
        $totalCheckouts = $statsQuery->count();
        
        $paidCheckouts = (clone $statsQuery)->whereHas('payment', function($q) {
                $q->where('payment_status', 'completed');
            })
            ->count();

        $unpaidCheckouts = $totalCheckouts - $paidCheckouts;

        return view('employee.checkout.index', compact(
            'bookings',
            'todayShift',
            'hasShift',
            'isActiveShift',
            'otherActiveShift',
            'hasOtherActiveShift',
            'totalCheckouts',
            'paidCheckouts',
            'unpaidCheckouts'
        ));
    }

    /**
     * Hiển thị chi tiết booking checkout
     */
    public function show($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $booking = Booking::with(['user', 'room', 'payment', 'payments', 'additionalCharges'])->findOrFail($id);

        // Kiểm tra booking có status hợp lệ
        // Cho phép xem pending nếu đã có payment (đã thanh toán)
        // Cho phép xem cancelled để xem thông tin và hoàn tiền
        $isValidStatus = in_array($booking->status, ['confirmed', 'checked_in', 'checked_out', 'completed', 'cancelled']);
        if (!$isValidStatus && $booking->status === 'pending') {
            // Cho phép xem pending nếu đã có payment
            $isValidStatus = $booking->payment && in_array($booking->payment->payment_status, ['pending', 'completed']);
        }
        
        if (!$isValidStatus) {
            return redirect()->route('admin.employee.checkout.index')
                ->with('error', 'Booking này không ở trạng thái hợp lệ để xem.');
        }

        // Cho phép xem booking có check-in hoặc checkout từ hôm nay trở đi
        // Hoặc đã checkout (để xem lại thông tin)
        $today = Carbon::today();
        $checkInDate = Carbon::parse($booking->check_in_date)->startOfDay();
        $checkOutDate = Carbon::parse($booking->check_out_date)->startOfDay();
        
        if (!in_array($booking->status, ['checked_out', 'completed']) && $checkInDate->lt($today) && $checkOutDate->lt($today)) {
            return redirect()->route('admin.employee.checkout.index')
                ->with('error', 'Booking này đã quá hạn (check-in và checkout đều trong quá khứ).');
        }

        // Tính toán thông tin checkout sớm để hiển thị
        $isEarlyCheckout = false;
        $daysEarly = 0;
        $actualNights = 0;
        $refundAmount = 0;
        $actualRoomPrice = 0;
        $originalPaymentCompleted = $booking->payment && $booking->payment->payment_status === 'completed';
        
        if ($booking->status !== 'completed' && $checkOutDate->gt($today)) {
            $isEarlyCheckout = true;
            $daysEarly = $today->diffInDays($checkOutDate);
            
            if ($daysEarly > 0 && $booking->room) {
                // Tính số ngày thực tế đã ở
                $actualNights = $checkInDate->diffInDays($today);
                if ($actualNights == 0) {
                    $actualNights = 1;
                }
                
                $pricePerNight = $booking->room->price_per_night;
                $actualRoomPrice = $actualNights * $pricePerNight;
                
                // Nếu đã thanh toán, tính số tiền cần hoàn
                if ($originalPaymentCompleted && $booking->payment) {
                    $refundAmount = $booking->payment->amount - $actualRoomPrice;
                }
            }
        }
        
        return view('employee.checkout.show', compact('booking', 'isEarlyCheckout', 'daysEarly', 'actualNights', 'refundAmount', 'actualRoomPrice', 'originalPaymentCompleted'));
    }

    /**
     * Check-in nhận phòng
     */
    public function checkIn($id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Kiểm tra ca làm việc - tìm ca active (không giới hạn theo ngày)
        // Ca làm việc không bị giới hạn - nếu status = 'active' thì luôn coi là active
        $activeShift = $admin->shifts()
            ->where('status', 'active')
            ->orderBy('shift_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->first();

        // Kiểm tra nếu có ca và ca đang active
        if (!$activeShift) {
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Bạn cần có ca làm việc đang hoạt động để thực hiện check-in.');
        }

        $booking = Booking::with(['user', 'room', 'payment'])->findOrFail($id);

        // Kiểm tra booking status hợp lệ để check-in
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Booking này không ở trạng thái hợp lệ để check-in.');
        }

        // Kiểm tra thanh toán
        if (!$booking->payment || $booking->payment->payment_status !== 'completed') {
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Khách hàng chưa thanh toán. Vui lòng yêu cầu khách thanh toán trước khi check-in.');
        }

        // Kiểm tra ngày check-in
        $checkInDate = Carbon::parse($booking->check_in_date)->startOfDay();
        $today = Carbon::today()->startOfDay();
        
        // Cho phép check-in sớm 1 ngày hoặc đúng ngày
        if ($checkInDate->gt($today->copy()->addDay())) {
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Chưa đến ngày check-in. Chỉ có thể check-in trước 1 ngày hoặc đúng ngày check-in.');
        }

        // Kiểm tra phòng có sẵn không
        if ($booking->room->status !== 'available' && $booking->room->status !== 'occupied') {
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Phòng đang ở trạng thái không hợp lệ để check-in.');
        }

        \DB::beginTransaction();
        try {
            // Cập nhật booking status
            $booking->update(['status' => 'checked_in']);

            // Cập nhật room status
            if ($booking->room->status === 'available') {
                $booking->room->update(['status' => 'occupied']);
            }

            // Gán shift_id nếu chưa có
            if (!$booking->shift_id && $activeShift) {
                $booking->update(['shift_id' => $activeShift->id]);
            }

            \DB::commit();

            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('success', 'Check-in thành công! Khách hàng đã nhận phòng.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Có lỗi xảy ra khi check-in: ' . $e->getMessage());
        }
    }

    /**
     * Checkout trả phòng
     */
    public function checkout(Request $request, $id)
    {
        $admin = auth('admin')->user();

        if (!$admin->isEmployee()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Kiểm tra ca làm việc - tìm ca active (không giới hạn theo ngày)
        // Ca làm việc không bị giới hạn - nếu status = 'active' thì luôn coi là active
        $activeShift = $admin->shifts()
            ->where('status', 'active')
            ->orderBy('shift_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->first();

        // Kiểm tra nếu có ca và ca đang active
        if (!$activeShift) {
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Bạn cần có ca làm việc đang hoạt động để thực hiện checkout.');
        }

        $booking = Booking::with(['user', 'room', 'payment'])->findOrFail($id);

        // Kiểm tra booking status hợp lệ để checkout
        if (!in_array($booking->status, ['confirmed', 'checked_in'])) {
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Booking này không ở trạng thái hợp lệ để checkout.');
        }

        // Kiểm tra thanh toán
        if (!$booking->payment || $booking->payment->payment_status !== 'completed') {
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Khách hàng chưa thanh toán. Vui lòng yêu cầu khách thanh toán trước khi checkout.');
        }

        // Validate phí phát sinh
        $validated = $request->validate([
            'additional_charges' => 'nullable|array',
            'additional_charges.*.service_name' => 'required_with:additional_charges|string|max:255',
            'additional_charges.*.quantity' => 'required_with:additional_charges|integer|min:1',
            'additional_charges.*.unit_price' => 'required_with:additional_charges|numeric|min:0',
            'additional_charges.*.notes' => 'nullable|string|max:500',
            'additional_charges_payment_method' => 'required_if:additional_charges,!=,null|in:cash,bank_transfer_qr',
            'additional_charges_payment_status' => 'required_if:additional_charges,!=,null|in:pending,completed',
            'calculate_early_checkout_penalty' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $totalAdditionalCharges = 0;
            $earlyCheckoutPenalty = 0;

            // Lưu ngày checkout dự kiến ban đầu để so sánh sau
            $originalCheckoutDate = Carbon::parse($booking->check_out_date)->startOfDay();
            $checkInDate = Carbon::parse($booking->check_in_date)->startOfDay();
            $today = Carbon::today()->startOfDay();
            $checkOutDate = $originalCheckoutDate;
            
            // Tính số ngày thực tế đã ở (từ check_in_date đến ngày checkout hôm nay)
            $actualNights = $checkInDate->diffInDays($today);
            if ($actualNights == 0) {
                $actualNights = 1; // Ít nhất 1 đêm
            }
            
            // Tính số ngày dư nếu checkout sớm
            $daysEarly = 0;
            $refundAmount = 0;
            $originalPaymentCompleted = $booking->payment && $booking->payment->payment_status === 'completed';
            
            if ($checkOutDate->gt($today)) {
                // Checkout sớm
                $daysEarly = $today->diffInDays($checkOutDate);
                
                if ($daysEarly > 0 && $booking->room) {
                    $pricePerNight = $booking->room->price_per_night;
                    
                    // Nếu đã thanh toán tiền phòng: tính lại tiền phòng theo số ngày thực tế và hoàn tiền cho số ngày dư
                    if ($originalPaymentCompleted && $booking->payment) {
                        // Tính tiền phòng thực tế (theo số ngày đã ở)
                        $actualRoomPrice = $actualNights * $pricePerNight;
                        
                        // Tính số tiền đã trả
                        $paidAmount = $booking->payment->amount;
                        
                        // Tính số tiền cần hoàn = tiền đã trả - tiền phòng thực tế
                        $refundAmount = $paidAmount - $actualRoomPrice;
                        
                        // Cập nhật total_price của booking theo số ngày thực tế
                        $booking->update([
                            'total_price' => $actualRoomPrice
                        ]);
                        
                        // Nếu có tiền cần hoàn, tạo refund request
                        if ($refundAmount > 0) {
                            // Tạo refund request tự động
                            \App\Models\RefundRequest::create([
                                'payment_id' => $booking->payment->id,
                                'booking_id' => $booking->id,
                                'user_id' => $booking->user_id,
                                'refund_amount' => $refundAmount,
                                'refund_method' => 'bank_transfer', // Mặc định, nhân viên sẽ cập nhật sau
                                'status' => 'pending',
                                'notes' => "Hoàn tiền do checkout sớm {$daysEarly} ngày. Số ngày thực tế: {$actualNights} đêm. Tiền phòng thực tế: " . number_format($actualRoomPrice) . " VNĐ.",
                            ]);
                            
                            // Cập nhật payment status thành refunded (một phần)
                            $booking->payment->update([
                                'payment_status' => 'refunded',
                                'notes' => ($booking->payment->notes ?? '') . "\n[CHECKOUT SỚM] Đã checkout sớm {$daysEarly} ngày. Số tiền cần hoàn: " . number_format($refundAmount) . " VNĐ.",
                            ]);
                        }
                    } else {
                        // Nếu chưa thanh toán: tính lại tiền phòng theo số ngày thực tế
                        $actualRoomPrice = $actualNights * $pricePerNight;
                        $booking->update([
                            'total_price' => $actualRoomPrice
                        ]);
                    }
                }
            }

            // Lưu các phí phát sinh khác
            if (isset($validated['additional_charges']) && is_array($validated['additional_charges'])) {
                foreach ($validated['additional_charges'] as $charge) {
                    if (!empty($charge['service_name']) && isset($charge['quantity']) && isset($charge['unit_price'])) {
                        $totalPrice = $charge['quantity'] * $charge['unit_price'];
                        $totalAdditionalCharges += $totalPrice;

                        BookingAdditionalCharge::create([
                            'booking_id' => $booking->id,
                            'service_name' => $charge['service_name'],
                            'quantity' => $charge['quantity'],
                            'unit_price' => $charge['unit_price'],
                            'total_price' => $totalPrice,
                            'notes' => $charge['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Tạo payment mới cho phí phát sinh (nếu có)
            if ($totalAdditionalCharges > 0) {
                // Kiểm tra xem booking đã thanh toán phòng chưa
                $originalPaymentCompleted = $booking->payment && $booking->payment->payment_status === 'completed';
                
                // Nếu đã thanh toán phòng rồi, chỉ thanh toán phí phát sinh
                // Payment amount chỉ tính cho phí phát sinh, không bao gồm tiền phòng
                $paymentAmount = $totalAdditionalCharges;
                
                // Vẫn cập nhật tổng tiền booking để tracking (giá phòng + phí phát sinh)
                // Nhưng payment chỉ tính cho phí phát sinh
                $booking->update([
                    'total_price' => $booking->total_price + $totalAdditionalCharges
                ]);

                // Tự động set completed nếu booking đã thanh toán, hoặc lấy từ form
                $paymentStatus = $originalPaymentCompleted 
                    ? 'completed' 
                    : ($validated['additional_charges_payment_status'] ?? 'completed');
                
                $paymentNotes = 'Thanh toán phí phát sinh khi checkout';
                if ($earlyCheckoutPenalty > 0) {
                    $paymentNotes .= ' (bao gồm phí checkout sớm)';
                }
                if ($originalPaymentCompleted) {
                    $paymentNotes .= ' - Tiền phòng đã thanh toán trước đó';
                }
                $paymentNotes .= ' - ' . ($paymentStatus === 'completed' ? 'Đã thanh toán' : 'Chờ thanh toán');
                
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $paymentAmount, // Chỉ tính phí phát sinh
                    'payment_method' => $validated['additional_charges_payment_method'] ?? 'cash',
                    'payment_status' => $paymentStatus,
                    'transaction_id' => 'ADD_' . $booking->id . '_' . time(),
                    'payment_date' => $paymentStatus === 'completed' ? now() : null,
                    'notes' => $paymentNotes,
                ]);
            }

            // Cập nhật check_out_date về ngày checkout thực tế (nếu checkout sớm hoặc muộn)
            $actualCheckoutDate = Carbon::today();
            $updateData = [
                'status' => 'completed',
                'check_out_date' => $actualCheckoutDate,
            ];
            
            // Cập nhật check_out_time nếu có
            if ($request->has('check_out_time')) {
                $updateData['check_out_time'] = $request->check_out_time;
            } else {
                // Mặc định là giờ hiện tại hoặc giờ checkout mặc định
                $updateData['check_out_time'] = now()->format('H:i');
            }
            
            $booking->update($updateData);

            // Cập nhật room status về available nếu đang occupied
            if ($booking->room && $booking->room->status === 'occupied') {
                $booking->room->update(['status' => 'available']);
            }

            // Gán shift_id nếu chưa có
            if (!$booking->shift_id && $activeShift) {
                $booking->update(['shift_id' => $activeShift->id]);
            }

            DB::commit();

            $message = 'Checkout thành công! Booking đã hoàn thành. Phòng đã được trả và chuyển về trạng thái rỗi.';
            
            // Thông báo nếu checkout sớm hoặc muộn
            if ($actualCheckoutDate->lt($originalCheckoutDate)) {
                $daysEarlyMsg = $actualCheckoutDate->diffInDays($originalCheckoutDate);
                $message .= " (Checkout sớm {$daysEarlyMsg} ngày so với dự kiến - Ngày checkout đã được cập nhật)";
                
                // Thông báo về hoàn tiền nếu có
                if ($refundAmount > 0) {
                    $message .= ". Đã tạo yêu cầu hoàn tiền: " . number_format($refundAmount) . " VNĐ.";
                } elseif ($originalPaymentCompleted) {
                    $message .= ". Tiền phòng đã được tính lại theo số ngày thực tế.";
                }
            } elseif ($actualCheckoutDate->gt($originalCheckoutDate)) {
                $daysLate = $originalCheckoutDate->diffInDays($actualCheckoutDate);
                $message .= " (Checkout muộn {$daysLate} ngày so với dự kiến - Ngày checkout đã được cập nhật)";
            } else {
                $message .= " (Ngày checkout đã được cập nhật)";
            }
            
            if ($totalAdditionalCharges > 0) {
                $paymentStatus = $validated['additional_charges_payment_status'] ?? 'completed';
                $message .= ' Tổng phí phát sinh: ' . number_format($totalAdditionalCharges) . ' VNĐ';
                if ($paymentStatus === 'completed') {
                    $message .= ' - Đã thanh toán.';
                } else {
                    $message .= ' - Chờ thanh toán.';
                }
            }

            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.employee.checkout.show', $id)
                ->with('error', 'Có lỗi xảy ra khi checkout: ' . $e->getMessage());
        }
    }
}
