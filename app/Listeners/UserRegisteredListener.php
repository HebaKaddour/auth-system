<?php

namespace App\Listeners;

use App\Mail\verifymail;
use App\Events\UserRegistered;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Helpers\VerificationCodeHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserRegisteredListener
{

    public function __construct()
    {
        //
    }

    public function handle(UserRegistered $event)
    {
        $user = $event->user;
        $code = VerificationCodeHelper::generate();
        $ipAddress = request()->ip();
        Cache::put($ipAddress.'_email', $user->email);
        Cache::remember($ipAddress, 60 * 3, function () use ($code ) {
           return $code;
        });
      Mail::to($user->email)->send(new verifymail($code));
    }
    }

