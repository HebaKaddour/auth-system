<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    public function verify(Request $request)
{
    $validator = Validator::make($request->all(), [
        'code' => 'required|string|min:6|max:255', // Adjust code length as needed
    ]);

    if ($validator->fails()) {
        return $validator->errors(); // Unprocessable Entity
    }

    $code = $request->input('code');

    $verificationCode = VerificationCode::where('code', $code)
        ->where('expires_at', '>=', now())
        ->first();

    if (!$verificationCode) {
        return "Invalid verification code or link expired";
    }

    $user = User::where('id', $verificationCode->user_id)->first();


    $user->markEmailAsVerified();
    $verificationCode->verified_at = now();
    $verificationCode->save();


    $successMessage = "Your email address has been verified at " . $verificationCode->verified_at->format('Y-m-d H:i:s');

    return view('verification.verified', ['message' => $successMessage]);
}
}
