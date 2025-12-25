<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RoomsTemplateExport implements FromArray, WithHeadings, WithDrawings, WithColumnWidths, WithStyles
{
    public function array(): array
    {
        return [
            ['101', 'Standard', '2', '500000', 'Phòng tiêu chuẩn với view đẹp', 'WiFi, TV, Điều hòa', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400', 'available'],
            ['102', 'Deluxe', '3', '800000', 'Phòng deluxe rộng rãi', 'WiFi, TV, Điều hòa, Minibar', 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=400', 'available'],
            ['201', 'Suite', '4', '1200000', 'Phòng suite sang trọng', 'WiFi, TV, Điều hòa, Minibar, Bồn tắm', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400', 'available'],
        ];
    }

    public function headings(): array
    {
        return [
            'Số phòng',
            'Loại phòng',
            'Sức chứa',
            'Giá/đêm (VNĐ)',
            'Mô tả',
            'Tiện nghi (phân cách bằng dấu phẩy)',
            'Link hình ảnh (URL hoặc đường dẫn file)',
            'Trạng thái',
        ];
    }

    public function drawings()
    {
        $drawings = [];
        
        // Tạo placeholder images hoặc sử dụng hình ảnh mẫu từ URL
        // Lưu ý: Với maatwebsite/excel, việc nhúng hình ảnh từ URL có thể phức tạp
        // Nên chúng ta sẽ để cột "Link hình ảnh" để người dùng có thể điền URL hoặc đường dẫn
        
        return $drawings;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Số phòng
            'B' => 15,  // Loại phòng
            'C' => 12,  // Sức chứa
            'D' => 18,  // Giá/đêm
            'E' => 40,  // Mô tả
            'F' => 35,  // Tiện nghi
            'G' => 50,  // Link hình ảnh
            'H' => 15,  // Trạng thái
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}

