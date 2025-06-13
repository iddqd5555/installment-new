<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentStatusUpdated extends Notification
{
    use Queueable;

    public $payment;
    public $status;

    public function __construct($payment, $status)
    {
        $this->payment = $payment;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->status == 'approved'
                ? 'การชำระเงินของคุณได้รับการอนุมัติ'
                : 'การชำระเงินของคุณถูกปฏิเสธ โปรดตรวจสอบใหม่อีกครั้ง',
            'payment_id' => $this->payment->id,
            'status' => $this->status,
        ];
    }

}
