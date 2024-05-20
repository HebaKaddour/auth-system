<?php

namespace App\Listeners;

use App\Mail\verifymail;
use App\Events\UserRegistered;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Mail;
use App\Helpers\VerificationCodeHelper;

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
        $expiresAt = now()->addMinutes(3); // 3 minutes expiration

        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt
        ]);
        $link = route('verification.verify', $code);

      Mail::to($user->email)->send(new verifymail($code,$link));
    }
    }

