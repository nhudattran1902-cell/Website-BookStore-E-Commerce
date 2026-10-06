<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mã OTP đăng nhập quản trị BOOK & BOX')
            ->greeting('Xác minh đăng nhập quản trị')
            ->line("Mã OTP của bạn là: {$this->code}")
            ->line('Mã có hiệu lực trong 5 phút và chỉ sử dụng được một lần.')
            ->line('Nếu bạn không thực hiện đăng nhập, hãy bỏ qua email này.');
    }
}
