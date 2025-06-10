<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\InstallmentRequest;

class InstallmentRequestStatusNotification extends Notification
{
    use Queueable;

    protected $installmentRequest;

    public function __construct(InstallmentRequest $installmentRequest)
    {
        $this->installmentRequest = $installmentRequest;
    }

    public function via($notifiable)
    {
        return ['database']; // เปลี่ยนเป็น ['mail','database'] ถ้าต้องการส่งอีเมล
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'คำขอผ่อนทองของคุณได้รับการอนุมัติแล้ว',
            'approved_gold_price' => $this->installmentRequest->approved_gold_price,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('แจ้งเตือนการอนุมัติคำขอผ่อนทอง')
                    ->line('คำขอของคุณได้รับการอนุมัติแล้ว')
                    ->line('ราคาทอง ณ เวลาที่อนุมัติ: ' . number_format($this->installmentRequest->approved_gold_price, 2) . ' บาท')
                    ->action('ดูรายละเอียด', url('/dashboard'))
                    ->line('ขอบคุณที่ใช้บริการค่ะ!');
    }
}
