<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentRejectedNotification extends Notification
{
    use Queueable;

    protected $payment;

    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "หลักฐานการชำระเงินของคุณถูกปฏิเสธ กรุณาอัปโหลดใหม่อีกครั้งค่ะ",
            'payment_id' => $this->payment->id,
        ];
    }
}
