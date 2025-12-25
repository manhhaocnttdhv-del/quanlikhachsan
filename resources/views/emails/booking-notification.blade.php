<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject ?? 'Thông báo' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #2a5298;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .booking-info {
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #2a5298;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Khách sạn - Thông báo</h2>
        </div>
        
        <div class="content">
            @if($type == 'check_in_reminder')
                <h3>Nhắc nhở Check-in hôm nay</h3>
                <p>Xin chào <strong>{{ $booking->user->name }}</strong>,</p>
                <p>Chúng tôi nhắc nhở bạn về booking check-in hôm nay:</p>
            @elseif($type == 'check_out_reminder')
                <h3>Nhắc nhở Check-out hôm nay</h3>
                <p>Xin chào <strong>{{ $booking->user->name }}</strong>,</p>
                <p>Chúng tôi nhắc nhở bạn về booking check-out hôm nay:</p>
            @elseif($type == 'unpaid_reminder')
                <h3>Nhắc nhở thanh toán</h3>
                <p>Xin chào <strong>{{ $booking->user->name }}</strong>,</p>
                <p>Booking của bạn chưa được thanh toán. Vui lòng thanh toán sớm:</p>
            @endif

            <div class="booking-info">
                <p><strong>Mã đặt phòng:</strong> #{{ $booking->id }}</p>
                <p><strong>Phòng:</strong> {{ $booking->room->room_number }} - {{ $booking->room->room_type }}</p>
                <p><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }} {{ $booking->check_in_time ?? '14:00' }}</p>
                <p><strong>Checkout:</strong> {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }} {{ $booking->check_out_time ?? '12:00' }}</p>
                <p><strong>Tổng tiền:</strong> {{ number_format($booking->total_price) }} VNĐ</p>
                @if($type == 'unpaid_reminder')
                    <p><strong>Trạng thái thanh toán:</strong> <span style="color: red;">Chưa thanh toán</span></p>
                @endif
            </div>

            <p>Trân trọng,<br>Khách sạn</p>
        </div>

        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống quản lý khách sạn.</p>
        </div>
    </div>
</body>
</html>

