<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'phone',
        'password',
        'id_card_number',
        'phone_verified_at',
        'is_admin',
        'identity_verification_status',
        'address',
        'workplace',
        'salary',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function username()
    {
        return 'phone';
    }

    public function installmentRequests()
    {
        return $this->hasMany(InstallmentRequest::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, InstallmentRequest::class);
    }
}
