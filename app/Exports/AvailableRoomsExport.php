<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AvailableRoomsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $rooms;

    public function __construct($rooms)
    {
        $this->rooms = $rooms;
    }

    public function collection()
    {
        return $this->rooms;
    }

    public function headings(): array
    {
        return [
            'Số phòng',
            'Loại phòng',
            'Sức chứa',
            'Giá/đêm (VNĐ)',
            'Trạng thái',
        ];
    }

    public function map($room): array
    {
        return [
            $room->room_number,
            $room->room_type,
            $room->capacity . ' người',
            number_format($room->price_per_night),
            'Trống',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 15,
            'D' => 20,
            'E' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
