<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InstallmentApproved extends Notification
{
    use Queueable;

    protected $installmentRequest;

    public function __construct($installmentRequest)
    {
        $this->installmentRequest = $installmentRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('การอนุมัติคำขอผ่อนทองของคุณ')
                    ->line('คำขอผ่อนทองของคุณได้รับการอนุมัติเรียบร้อยแล้ว!')
                    ->action('ดูรายละเอียด', url('/dashboard'))
                    ->line('ขอบคุณที่ใช้บริการเรา!');
    }

    public function toArray($notifiable)
    {
        return [
            'installment_request_id' => $this->installmentRequest->id,
            'message' => 'คำขอผ่อนทองของคุณได้รับการอนุมัติแล้ว'
        ];
    }
}

