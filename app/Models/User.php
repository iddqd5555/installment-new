<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'id_card_number',
        'identity_verification_status',
        'email',
        'address',
        'date_of_birth',
        'gender',
        'salary',
        'workplace',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'id_card_image',
        'slip_salary_image',
        'additional_documents',
        'is_admin',
        'role',
        'status',
        'password',
        'two_factor_enabled',
        'last_login_at',
        'last_login_ip',
        'latitude',
        'longitude',
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
