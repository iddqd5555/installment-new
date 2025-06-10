<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstallmentDueReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('แจ้งเตือนชำระค่างวดผ่อนทอง')
        ->line('ถึงกำหนดชำระเงินผ่อนทองของคุณในอีก 3 วันข้างหน้าค่ะ')
        ->action('ชำระเงิน', route('dashboard'))
        ->line('กรุณาชำระให้ตรงเวลาเพื่อหลีกเลี่ยงค่าปรับค่ะ');
}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
