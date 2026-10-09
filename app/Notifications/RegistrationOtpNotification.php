<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationOtpNotification extends Notification
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
            ->subject('Mã OTP xác minh đăng ký BOOK & BOX')
            ->greeting('Xin chào '.$notifiable->ho_ten)
            ->line("Mã OTP đăng ký của bạn là: {$this->code}")
            ->line('Mã có hiệu lực trong 10 phút và chỉ sử dụng được một lần.')
            ->line('Nếu bạn không đăng ký tài khoản, hãy bỏ qua email này.');
    }
}
