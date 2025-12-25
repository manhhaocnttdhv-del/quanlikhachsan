<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OccupiedRoomsExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    protected $rooms;

    public function __construct($rooms)
    {
        $this->rooms = $rooms;
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->rooms as $room) {
            foreach ($room->bookings as $booking) {
                $totalAmount = 0;
                if ($booking->payment && $booking->payment->payment_status === 'completed') {
                    $totalAmount = $booking->payment->amount;
                }
                
                $data[] = [
                    $room->room_number,
                    $booking->user->name ?? '-',
                    $booking->user->email ?? '-',
                    $booking->user->phone ?? '-',
                    $booking->check_in_date->format('d/m/Y'),
                    $booking->check_out_date->format('d/m/Y'),
                    $this->getStatusText($booking->status),
                    number_format($totalAmount, 0, ',', '.') . ' VNĐ',
                ];
            }
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Số phòng',
            'Khách hàng',
            'Email',
            'Số điện thoại',
            'Ngày check-in',
            'Ngày check-out',
            'Trạng thái',
            'Tổng tiền',
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
            'A' => 15,
            'B' => 25,
            'C' => 30,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 20,
            'H' => 20,
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
