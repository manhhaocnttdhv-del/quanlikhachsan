<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DashboardExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $result = [];
        
        // Thông tin filter
        $dateRange = $this->data['dateRange'] ?? [];
        
        $result[] = ['BÁO CÁO DASHBOARD'];
        if ($dateRange['start'] && $dateRange['end']) {
            $result[] = [
                'Từ: ' . $dateRange['start']->format('d/m/Y'),
                'Đến: ' . $dateRange['end']->format('d/m/Y')
            ];
        } else {
            $result[] = ['Khoảng thời gian: Tất cả'];
        }
        $result[] = []; // Dòng trống
        
        // Thống kê tổng quan
        $result[] = ['THỐNG KÊ TỔNG QUAN'];
        $result[] = [
            'Tổng doanh thu',
            number_format($this->data['revenueStats']['filtered'] ?? 0) . ' VNĐ'
        ];
        $result[] = [
            'Tổng số phòng',
            $this->data['stats']['total_rooms'] ?? 0
        ];
        $result[] = [
            'Tổng số khách hàng',
            $this->data['stats']['total_customers'] ?? 0
        ];
        $result[] = [
            'Tổng số đặt phòng',
            $this->data['stats']['total_bookings'] ?? 0
        ];
        $result[] = []; // Dòng trống
        
        // Danh sách bookings
        $result[] = ['DANH SÁCH ĐẶT PHÒNG'];
        $result[] = [
            'ID',
            'Khách hàng',
            'Email',
            'Số phòng',
            'Loại phòng',
            'Ngày nhận',
            'Ngày trả',
            'Tổng tiền',
            'Trạng thái',
            'Ngày đặt'
        ];
        
        foreach ($this->data['bookings'] ?? [] as $booking) {
            $result[] = [
                $booking->id,
                $booking->user->name ?? '-',
                $booking->user->email ?? '-',
                $booking->room->room_number ?? '-',
                $booking->room->room_type ?? '-',
                $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : '-',
                $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : '-',
                number_format($booking->total_price) . ' VNĐ',
                $this->getStatusText($booking->status),
                $booking->created_at ? $booking->created_at->format('d/m/Y H:i') : '-',
            ];
        }
        
        $result[] = []; // Dòng trống
        
        // Danh sách payments
        $result[] = ['DANH SÁCH THANH TOÁN'];
        $result[] = [
            'ID',
            'Mã đặt phòng',
            'Khách hàng',
            'Email',
            'Số phòng',
            'Số tiền',
            'Phương thức',
            'Trạng thái',
            'Ngày thanh toán',
            'Mã giao dịch'
        ];
        
        foreach ($this->data['payments'] ?? [] as $payment) {
            $result[] = [
                $payment->id,
                $payment->booking_id ?? '-',
                $payment->booking->user->name ?? '-',
                $payment->booking->user->email ?? '-',
                $payment->booking->room->room_number ?? '-',
                number_format($payment->amount) . ' VNĐ',
                $this->getPaymentMethodText($payment->payment_method),
                $this->getPaymentStatusText($payment->payment_status),
                $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : '-',
                $payment->transaction_id ?? '-',
            ];
        }
        
        return $result;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Dashboard Report';
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

    private function getPaymentMethodText($method): string
    {
        return match($method) {
            'cash' => 'Tiền mặt',
            'credit_card' => 'Thẻ tín dụng',
            'bank_transfer' => 'Chuyển khoản',
            'bank_transfer_qr' => 'QR Chuyển khoản',
            'vnpay' => 'VNPay',
            'momo' => 'MoMo',
            default => $method,
        };
    }

    private function getPaymentStatusText($status): string
    {
        return match($status) {
            'pending' => 'Chờ thanh toán',
            'completed' => 'Đã thanh toán',
            'failed' => 'Thất bại',
            'refunded' => 'Đã hoàn tiền',
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
            'E' => 20,
            'F' => 15,
            'G' => 15,
            'H' => 20,
            'I' => 20,
            'J' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true],
            ],
            5 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0'],
                ],
            ],
        ];
    }
}
