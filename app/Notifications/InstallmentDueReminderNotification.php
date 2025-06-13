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
    public $installmentRequest;

    public function __construct($installmentRequest)
    {
        $this->installmentRequest = $installmentRequest;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'ถึงกำหนดชำระเงินในอีก 3 วัน กรุณาชำระเงินให้ตรงเวลา',
            'due_date' => $this->installmentRequest->next_payment_date,
        ];
    }
}
