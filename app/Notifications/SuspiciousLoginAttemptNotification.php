<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuspiciousLoginAttemptNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $failedAttempts,
        public string $ipAddress,
        public int $lockoutSeconds,
    ) {}

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
            ->subject('Cảnh báo: phát hiện nhiều lần đăng nhập thất bại')
            ->greeting('Xin chào '.$notifiable->ho_ten)
            ->line("Đã có {$this->failedAttempts} lần đăng nhập thất bại vào tài khoản của bạn.")
            ->line("Địa chỉ IP gần nhất: {$this->ipAddress}")
            ->line("Hệ thống tạm khóa đăng nhập trong {$this->lockoutSeconds} giây.")
            ->line('Nếu không phải bạn thực hiện, hãy đặt lại mật khẩu và liên hệ bộ phận hỗ trợ.');
    }
}
