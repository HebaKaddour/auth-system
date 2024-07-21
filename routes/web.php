<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;


Route::get('/', function () {
    return view('welcome');
});
