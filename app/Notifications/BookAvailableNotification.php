<?php

namespace App\Notifications;

use App\Models\Sach;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookAvailableNotification extends Notification
{
    public function __construct(public Sach $book) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable instanceof MustVerifyEmail && $notifiable->hasVerifiedEmail()) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Sách bạn theo dõi đã có hàng - BOOK & BOX')
            ->greeting('Sách đã có hàng')
            ->line("{$this->book->tieu_de} hiện đã có hàng trong kho.")
            ->action('Xem sách', route('books.show', $this->book->id))
            ->line('Số lượng có thể thay đổi theo đơn đặt hàng.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Sách đã có hàng',
            'message' => "{$this->book->tieu_de} hiện đã có hàng. Bạn có thể xem và đặt mua.",
            'book_id' => $this->book->id,
        ];
    }
}
