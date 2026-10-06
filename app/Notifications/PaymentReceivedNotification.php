<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    public function __construct(
        public int $orderId,
        public string $orderCode,
        public string $paymentMethod,
        public int $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $methodName = $this->paymentMethod === 'BankTransfer' ? 'VietQR' : $this->paymentMethod;

        return [
            'title' => 'Thanh toán demo thành công',
            'message' => "Đơn hàng #{$this->orderCode} đã được mô phỏng thanh toán qua {$methodName}.",
            'order_id' => $this->orderId,
            'order_code' => $this->orderCode,
            'payment_method' => $this->paymentMethod,
            'amount' => $this->amount,
            'is_demo' => true,
        ];
    }
}
