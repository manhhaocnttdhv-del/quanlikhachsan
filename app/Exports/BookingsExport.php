<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as PhpSpreadsheetWorksheet;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query ?? Booking::with(['user', 'room']);
    }

    public function collection()
    {
        // Đảm bảo query được thực thi đúng cách
        if ($this->query instanceof \Illuminate\Database\Eloquent\Builder) {
            return $this->query->get();
        }
        
        // Nếu query là collection, trả về trực tiếp
        return $this->query;
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
            'Tổng tiền',
            'Trạng thái',
            'Ngày đặt',
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->id,
            $booking->user->name ?? '-',
            $booking->user->email ?? '-',
            $booking->room->room_number ?? '-',
            $booking->room->room_type ?? '-',
            $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : '-',
            $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : '-',
            ($booking->check_in_date && $booking->check_out_date) 
                ? $booking->check_in_date->diffInDays($booking->check_out_date) 
                : '-',
            $booking->number_of_guests ?? '-',
            $booking->total_price ? number_format($booking->total_price) . ' VNĐ' : '-',
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
            'A' => 10,  // ID
            'B' => 20,  // Khách hàng
            'C' => 25,  // Email
            'D' => 15,  // Số phòng
            'E' => 20,  // Loại phòng
            'F' => 15,  // Ngày nhận phòng
            'G' => 15,  // Ngày trả phòng
            'H' => 10,  // Số đêm
            'I' => 10,  // Số người
            'J' => 15,  // Tổng tiền
            'K' => 15,  // Trạng thái
            'L' => 20,  // Ngày đặt
        ];
    }

    public function styles(PhpSpreadsheetWorksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
