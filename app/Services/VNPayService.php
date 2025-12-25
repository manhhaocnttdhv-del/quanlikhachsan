<?php

namespace App\Services;

class VNPayService
{
    protected $vnp_TmnCode;
    protected $vnp_HashSecret;
    protected $vnp_Url;
    protected $vnp_ReturnUrl;

    public function __construct()
    {
        $this->vnp_TmnCode = config('vnpay.tmn_code', '1ZHAUTE2');
        $this->vnp_HashSecret = config('vnpay.hash_secret', 'E3NW9PS4KDIRQVW8QRIEF159JKU8FSNG');
        $this->vnp_Url = config('vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $this->vnp_ReturnUrl = url('/payments/vnpay/return');
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPaymentUrl($orderId, $amount, $orderDescription, $orderType = 'other', $locale = 'vn')
    {
        $vnp_TxnRef = $orderId; // Mã đơn hàng
        $vnp_Amount = $amount * 100; // VNPay yêu cầu số tiền nhân 100
        $vnp_Locale = $locale;
        $vnp_IpAddr = request()->ip();
        $vnp_CreateDate = date('YmdHis');

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $orderDescription,
            "vnp_OrderType" => $orderType,
            "vnp_ReturnUrl" => $this->vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        if (isset($this->vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Xác thực callback từ VNPay
     */
    public function validateCallback($data)
    {
        $vnp_SecureHash = $data['vnp_SecureHash'] ?? '';
        unset($data['vnp_SecureHash']);

        ksort($data);
        $i = 0;
        $hashdata = "";
        foreach ($data as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);

        return $secureHash === $vnp_SecureHash;
    }
}

