<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocationLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'name', 'phone', 'ip', 'latitude', 'longitude', 'vpn_status', 'notes'
    ];
}
