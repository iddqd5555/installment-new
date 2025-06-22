<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SCBController;

Route::post('/payment/scb/callback', [SCBController::class, 'callback']);
