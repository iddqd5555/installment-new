<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentUploaded extends Notification
{
    use Queueable;

    public $payment;

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
            'message' => 'คุณได้อัปโหลดสลิปเรียบร้อยแล้ว รอการอนุมัติจากแอดมิน',
            'payment_id' => $this->payment->id,
            'status' => $this->payment->status,
        ];
    }
}
