<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AdvancePaymentNotification extends Notification
{
    protected $advanceAmount;

    public function __construct($advanceAmount)
    {
        $this->advanceAmount = $advanceAmount;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'คุณมียอดชำระล่วงหน้า '.number_format($this->advanceAmount, 2).' บาท',
            'date' => now()->toDateString(),
            'type' => 'advance_payment',
        ];
    }
}
