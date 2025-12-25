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

class RecentBookingsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $bookings;

    public function __construct($bookings)
    {
        $this->bookings = $bookings;
    }

    public function collection()
    {
        return $this->bookings;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Khách hàng',
            'Email',
            'Số phòng',
            'Loại phòng',
            'Ngày nhận phòng',
            'Ngày trả phòng',
            'Số đêm',
            'Số người',
            'Tổng tiền (VNĐ)',
            'Trạng thái',
            'Ngày đặt',
        ];
    }

    public function map($booking): array
    {
        $nights = 0;
        if ($booking->check_in_date && $booking->check_out_date) {
            $nights = $booking->check_in_date->diffInDays($booking->check_out_date);
        }

        return [
            $booking->id,
            $booking->user->name ?? '-',
            $booking->user->email ?? '-',
            $booking->room->room_number ?? '-',
            $booking->room->room_type ?? '-',
            $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : '-',
            $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : '-',
            $nights,
            $booking->number_of_guests ?? '-',
            number_format($booking->total_price),
            $this->getStatusText($booking->status),
            $booking->created_at ? $booking->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    private function getStatusText($status): string
    {
        return match($status) {
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã nhận phòng',
            'checked_out' => 'Đã trả phòng',
            'cancelled' => 'Đã hủy',
            default => $status,
        };
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 20,
            'C' => 25,
            'D' => 15,
            'E' => 20,
            'F' => 15,
            'G' => 15,
            'H' => 10,
            'I' => 10,
            'J' => 15,
            'K' => 15,
            'L' => 20,
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
