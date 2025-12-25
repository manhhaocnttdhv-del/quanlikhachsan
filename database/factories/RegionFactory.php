<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition()
    {
        $types = ['Quận', 'Huyện', 'Phường', 'Xã'];

        // Danh sách các khối (block), bạn có thể thay thế hoặc thêm vào các khối thực tế hơn
        $blocks = [
            'Tổ dân phố 1',
            'Tổ dân phố 2',
            'Khu phố 3',
            'Khu phố 4',
            'Tổ dân phố 5',
            'Tổ dân phố 6',
            'Khu phố 7',
            'Khu phố 8',
            'Tổ dân phố 9',
            'Khu phố 10'
        ];

        return [
            'name' => $this->faker->city(),  // Tạo tên khu vực với một tên thành phố hoặc địa danh ngẫu nhiên
            'type' => $this->faker->randomElement($types),  // Chọn loại khu vực ngẫu nhiên từ danh sách
            'block' => $this->faker->randomElement($blocks),  // Chọn khối từ danh sách các tổ dân phố, khu phố
        ];
    }
}
