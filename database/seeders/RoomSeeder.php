<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            // Standard Rooms
            [
                'room_number' => '101',
                'room_type' => 'Standard',
                'capacity' => 2,
                'price_per_night' => 500000,
                'description' => 'Phòng tiêu chuẩn với 1 giường đôi, phù hợp cho 2 người',
                'amenities' => ['TV', 'WiFi', 'Điều hòa', 'Tủ lạnh'],
                'status' => 'available',
            ],
            [
                'room_number' => '102',
                'room_type' => 'Standard',
                'capacity' => 2,
                'price_per_night' => 500000,
                'description' => 'Phòng tiêu chuẩn với 2 giường đơn, phù hợp cho 2 người',
                'amenities' => ['TV', 'WiFi', 'Điều hòa', 'Tủ lạnh'],
                'status' => 'available',
            ],
            [
                'room_number' => '103',
                'room_type' => 'Standard',
                'capacity' => 2,
                'price_per_night' => 500000,
                'description' => 'Phòng tiêu chuẩn với 1 giường đôi',
                'amenities' => ['TV', 'WiFi', 'Điều hòa', 'Tủ lạnh'],
                'status' => 'available',
            ],

            // Deluxe Rooms
            [
                'room_number' => '201',
                'room_type' => 'Deluxe',
                'capacity' => 3,
                'price_per_night' => 800000,
                'description' => 'Phòng cao cấp với view đẹp, 1 giường đôi và 1 giường đơn',
                'amenities' => ['TV', 'WiFi', 'Điều hòa', 'Tủ lạnh', 'Minibar', 'Ban công'],
                'status' => 'available',
            ],
            [
                'room_number' => '202',
                'room_type' => 'Deluxe',
                'capacity' => 3,
                'price_per_night' => 800000,
                'description' => 'Phòng cao cấp rộng rãi với view thành phố',
                'amenities' => ['TV', 'WiFi', 'Điều hòa', 'Tủ lạnh', 'Minibar', 'Ban công'],
                'status' => 'available',
            ],
            [
                'room_number' => '203',
                'room_type' => 'Deluxe',
                'capacity' => 3,
                'price_per_night' => 800000,
                'description' => 'Phòng cao cấp với đầy đủ tiện nghi',
                'amenities' => ['TV', 'WiFi', 'Điều hòa', 'Tủ lạnh', 'Minibar', 'Ban công'],
                'status' => 'available',
            ],

            // Suite Rooms
            [
                'room_number' => '301',
                'room_type' => 'Suite',
                'capacity' => 4,
                'price_per_night' => 1200000,
                'description' => 'Phòng suite sang trọng với phòng khách riêng',
                'amenities' => ['TV', 'WiFi', 'Điều hòa', 'Tủ lạnh', 'Minibar', 'Ban công', 'Bồn tắm', 'Phòng khách'],
                'status' => 'available',
            ],
            [
                'room_number' => '302',
                'room_type' => 'Suite',
                'capacity' => 4,
                'price_per_night' => 1200000,
                'description' => 'Phòng suite với 2 phòng ngủ',
                'amenities' => ['TV', 'WiFi', 'Điều hòa', 'Tủ lạnh', 'Minibar', 'Ban công', 'Bồn tắm', 'Phòng khách'],
                'status' => 'available',
            ],

            // VIP Rooms
            [
                'room_number' => '401',
                'room_type' => 'VIP',
                'capacity' => 6,
                'price_per_night' => 2000000,
                'description' => 'Phòng VIP cao cấp nhất với view panorama',
                'amenities' => ['TV 4K', 'WiFi tốc độ cao', 'Điều hòa', 'Tủ lạnh', 'Minibar', 'Ban công lớn', 'Bồn tắm Jacuzzi', 'Phòng khách', 'Bếp nhỏ'],
                'status' => 'available',
            ],
            [
                'room_number' => '402',
                'room_type' => 'VIP',
                'capacity' => 6,
                'price_per_night' => 2000000,
                'description' => 'Phòng VIP sang trọng với đầy đủ tiện nghi 5 sao',
                'amenities' => ['TV 4K', 'WiFi tốc độ cao', 'Điều hòa', 'Tủ lạnh', 'Minibar', 'Ban công lớn', 'Bồn tắm Jacuzzi', 'Phòng khách', 'Bếp nhỏ'],
                'status' => 'available',
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
