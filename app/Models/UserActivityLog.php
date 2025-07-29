<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'activity_type', 'ip', 'latitude', 'longitude', 'vpn_status', 'notes'
    ];
}
