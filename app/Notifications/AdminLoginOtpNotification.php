<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginOtpNotification extends Notification
{
    public function __construct(
        public string $code,
        public int $expiresInMinutes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mã OTP đăng nhập nhân viên - BOOK & BOX')
            ->greeting('Xin chào '.($notifiable->ho_ten ?? ''))
            ->line('Mã xác minh đăng nhập của bạn là: '.$this->code)
            ->line('Mã có hiệu lực trong '.$this->expiresInMinutes.' phút và chỉ sử dụng được một lần.')
            ->line('Nếu bạn không thực hiện đăng nhập, hãy bỏ qua email này và liên hệ quản trị viên nếu cần.');
    }
}
