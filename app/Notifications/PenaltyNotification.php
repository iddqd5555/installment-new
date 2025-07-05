<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PenaltyNotification extends Notification
{
    protected $penaltyAmount;

    public function __construct($penaltyAmount)
    {
        $this->penaltyAmount = $penaltyAmount;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'คุณมีค่าปรับจำนวน '.number_format($this->penaltyAmount, 2).' บาท กรุณาชำระโดยเร็ว',
            'date' => now()->toDateString(),
            'type' => 'penalty',
        ];
    }
}
