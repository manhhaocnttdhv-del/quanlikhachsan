<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as PhpSpreadsheetWorksheet;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query ?? Payment::with(['booking.user', 'booking.room']);
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
            'Mã đặt phòng',
            'Khách hàng',
            'Email',
            'Số phòng',
            'Số tiền',
            'Phương thức thanh toán',
            'Trạng thái',
            'Ngày thanh toán',
            'Mã giao dịch',
            'Ghi chú',
            'Ngày tạo',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->booking_id ?? '-',
            $payment->booking->user->name ?? '-',
            $payment->booking->user->email ?? '-',
            $payment->booking->room->room_number ?? '-',
            $payment->amount ? number_format($payment->amount) . ' VNĐ' : '-',
            $this->getPaymentMethodText($payment->payment_method ?? ''),
            $this->getStatusText($payment->payment_status ?? ''),
            $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : '-',
            $payment->transaction_id ?? '-',
            $payment->notes ?? '-',
            $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    private function getPaymentMethodText($method): string
    {
        return match($method) {
            'cash' => 'Tiền mặt',
            'credit_card' => 'Thẻ tín dụng',
            'bank_transfer' => 'Chuyển khoản',
            'bank_transfer_qr' => 'QR Chuyển khoản',
            'momo' => 'MoMo',
            'vnpay' => 'VNPay',
            default => $method,
        };
    }

    private function getStatusText($status): string
    {
        return match($status) {
            'pending' => 'Chờ xử lý',
            'completed' => 'Hoàn thành',
            'failed' => 'Thất bại',
            'refunded' => 'Đã hoàn tiền',
            default => $status,
        };
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 15,  // Mã đặt phòng
            'C' => 20,  // Khách hàng
            'D' => 25,  // Email
            'E' => 15,  // Số phòng
            'F' => 15,  // Số tiền
            'G' => 20,  // Phương thức thanh toán
            'H' => 15,  // Trạng thái
            'I' => 20,  // Ngày thanh toán
            'J' => 20,  // Mã giao dịch
            'K' => 30,  // Ghi chú
            'L' => 20,  // Ngày tạo
        ];
    }

    public function styles(PhpSpreadsheetWorksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
