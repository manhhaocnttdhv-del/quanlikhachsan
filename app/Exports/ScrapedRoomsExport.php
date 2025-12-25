<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ScrapedRoomsExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    protected $rooms;

    public function __construct(array $rooms)
    {
        $this->rooms = $rooms;
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->rooms as $room) {
            $amenities = '';
            if (isset($room['amenities']) && is_array($room['amenities'])) {
                $amenities = implode(', ', $room['amenities']);
            } elseif (isset($room['amenities']) && is_string($room['amenities'])) {
                $amenities = $room['amenities'];
            }
            
            $imageUrl = '';
            if (isset($room['images']) && is_array($room['images']) && !empty($room['images'])) {
                $imageUrl = $room['images'][0]; // Lấy ảnh đầu tiên
            } elseif (isset($room['image']) && is_string($room['image'])) {
                $imageUrl = $room['image'];
            }
            
            $data[] = [
                $room['room_number'] ?? '',
                $room['room_type'] ?? 'Standard',
                $room['capacity'] ?? 2,
                $room['price_per_night'] ?? 0,
                $room['description'] ?? '',
                $amenities,
                $imageUrl,
                'available', // Default status
            ];
        }
        
        return $data;
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

