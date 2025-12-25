<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\BookingAdditionalCharge;
use Illuminate\Support\Facades\DB;

class BookingAdditionalChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách các dịch vụ phát sinh phổ biến
        $services = [
            [
                'name' => 'Nước uống',
                'unit_price_range' => [10000, 20000],
                'quantity_range' => [1, 5],
                'notes' => ['Nước suối', 'Nước ngọt', 'Nước khoáng'],
            ],
            [
                'name' => 'Mì tôm',
                'unit_price_range' => [15000, 25000],
                'quantity_range' => [1, 4],
                'notes' => ['Mì tôm thường', 'Mì tôm chua cay', 'Mì tôm hải sản'],
            ],
            [
                'name' => 'Giặt đồ',
                'unit_price_range' => [50000, 100000],
                'quantity_range' => [1, 2],
                'notes' => ['Giặt thường', 'Giặt khô', 'Giặt nhanh'],
            ],
            [
                'name' => 'Đồ ăn nhẹ',
                'unit_price_range' => [30000, 80000],
                'quantity_range' => [1, 3],
                'notes' => ['Bánh kẹo', 'Snack', 'Trái cây'],
            ],
            [
                'name' => 'Dịch vụ phòng',
                'unit_price_range' => [20000, 50000],
                'quantity_range' => [1, 3],
                'notes' => ['Dọn phòng thêm', 'Thay ga gối', 'Dịch vụ phòng 24/7'],
            ],
            [
                'name' => 'Đồ uống có cồn',
                'unit_price_range' => [50000, 200000],
                'quantity_range' => [1, 4],
                'notes' => ['Bia', 'Rượu', 'Cocktail'],
            ],
            [
                'name' => 'Đồ ăn sáng',
                'unit_price_range' => [80000, 150000],
                'quantity_range' => [1, 2],
                'notes' => ['Bữa sáng buffet', 'Bữa sáng phục vụ tận phòng'],
            ],
            [
                'name' => 'Dịch vụ spa',
                'unit_price_range' => [200000, 500000],
                'quantity_range' => [1, 2],
                'notes' => ['Massage', 'Xông hơi', 'Chăm sóc da'],
            ],
            [
                'name' => 'Dịch vụ giặt ủi',
                'unit_price_range' => [30000, 80000],
                'quantity_range' => [1, 3],
                'notes' => ['Ủi áo sơ mi', 'Ủi quần', 'Giặt ủi combo'],
            ],
            [
                'name' => 'Đồ dùng vệ sinh',
                'unit_price_range' => [15000, 40000],
                'quantity_range' => [1, 5],
                'notes' => ['Kem đánh răng', 'Dầu gội', 'Sữa tắm', 'Khăn tắm'],
            ],
        ];

        // Lấy các booking đã checkout hoặc checked_in (có thể có phí phát sinh)
        $bookings = Booking::whereIn('status', ['checked_in', 'checked_out'])
            ->whereHas('payment', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->inRandomOrder()
            ->limit(20) // Tạo phí phát sinh cho 20 booking ngẫu nhiên
            ->get();

        if ($bookings->isEmpty()) {
            $this->command->warn('Không tìm thấy booking nào để tạo phí phát sinh. Vui lòng tạo booking trước.');
            return;
        }

        $this->command->info('Đang tạo phí phát sinh cho ' . $bookings->count() . ' booking...');

        foreach ($bookings as $booking) {
            // Mỗi booking có 1-4 dịch vụ phát sinh ngẫu nhiên
            $numberOfServices = rand(1, min(4, count($services)));
            
            // Chọn ngẫu nhiên các dịch vụ (không trùng lặp)
            $serviceIndices = array_rand($services, $numberOfServices);
            
            // Đảm bảo $serviceIndices là array
            if (!is_array($serviceIndices)) {
                $serviceIndices = [$serviceIndices];
            }

            $totalAdditionalCharges = 0;

            foreach ($serviceIndices as $serviceIndex) {
                $service = $services[$serviceIndex];
                
                // Random số lượng và giá
                $quantity = rand($service['quantity_range'][0], $service['quantity_range'][1]);
                $unitPrice = rand($service['unit_price_range'][0], $service['unit_price_range'][1]);
                $totalPrice = $quantity * $unitPrice;
                $totalAdditionalCharges += $totalPrice;

                // Random note từ danh sách
                $noteIndex = array_rand($service['notes']);
                $note = $service['notes'][$noteIndex];

                BookingAdditionalCharge::create([
                    'booking_id' => $booking->id,
                    'service_name' => $service['name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'notes' => $note,
                ]);
            }

            // Cập nhật tổng tiền booking
            $booking->update([
                'total_price' => $booking->total_price + $totalAdditionalCharges
            ]);

            $this->command->info("✓ Đã tạo {$numberOfServices} dịch vụ phát sinh cho booking #{$booking->id} (Tổng: " . number_format($totalAdditionalCharges) . " VNĐ)");
        }

        $this->command->info('✓ Hoàn thành! Đã tạo phí phát sinh cho ' . $bookings->count() . ' booking.');
    }
}
