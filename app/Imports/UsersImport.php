<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{
    public function model(array $row)
    {
            return new User([
                'name' => $row[0], // Giả sử cột đầu tiên là tên
                'email' => $row[1], // Cột thứ ba là email
                'address' => $row[2], // Cột thứ năm là địa chỉ
                'phone' => $row[3], // Cột thứ sáu là số điện thoại
                'members_count' => $row[4], // Cột thứ bảy là số lượng thành viên
            ]);
    }
}