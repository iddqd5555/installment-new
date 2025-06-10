<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentApprovedNotification extends Notification
{
    use Queueable;

    protected $payment;

    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['database']; // เก็บไว้ในฐานข้อมูล notifications
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "การชำระเงินของคุณได้รับการอนุมัติเรียบร้อยแล้วค่ะ!",
            'payment_id' => $this->payment->id,
        ];
    }
}
