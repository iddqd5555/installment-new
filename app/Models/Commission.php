<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'total_collected',
        'commission_rate',
        'commission_amount',
        'calculation_date',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
