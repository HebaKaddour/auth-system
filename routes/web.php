<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/email/verify/{code}', [VerificationController::class,'verify'])->name('verification.verify');
