<?php

namespace App\Services;

use App\Models\UserActivityLog;

class ActivityLogger
{
    public static function log($user, $activity_type, $ip, $latitude, $longitude, $vpn_status, $notes = '')
    {
        if (!$user) return;
        UserActivityLog::create([
            'user_id' => $user->id,
            'activity_type' => $activity_type,
            'ip' => $ip,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'vpn_status' => $vpn_status,
            'notes' => $notes,
        ]);
    }
}
