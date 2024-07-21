<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\verifymail;
use App\Enums\TokenAbility;
use Illuminate\Http\Request;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Helpers\VerificationCodeHelper;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Support\Facades\Auth;

class UserAuthController extends Controller
{

    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->phone_number = $data['phone_number'];


        if($request->hasFile('profile_photo')){
            $fileName = $this->uploadImage($request->file('profile_photo'),'uploads', 'photos');
            $user->profile_photo =$fileName;
        }

        if($request->hasFile('certificate')){
            $fileName = $this->uploadImage($request->file('certificate'), 'uploads','certificates');
           $user->certificate  =$fileName;
        }

        $user->save();
        event(new UserRegistered($user));
        return $this->successResponse($user, 'User created.', 201);
    }

    public function login(LoginUserRequest $request)
    {

        if (!auth()->attempt($request->only('email', 'password','phone_number'))) {
            return $this->errorResponse('unauthenticated',401);
        }

        $user = auth()->user();
        if (!$user->email_verified_at || $user->email !== $request->email) {
            return $this->errorResponse('Please verify your email address to login.', 403);
          }

        if ($user) {
            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            $data = [
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $refreshToken ? $refreshToken->plainTextToken : null
            ];
            return $this->successResponse($data, 'Logged in successfully.', 200);
        }
    }

    public function logout(Request $request)
    {
        $request->user('sanctum')->currentAccessToken()->delete();
        return $this->successResponse('success', 'Logged out successfully.', 200);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
        $refresh_Token = $refreshToken->plainTextToken;
        return $this->successResponse($refresh_Token, 'New refresh token created.', 200);
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('user not found',404);
         }

        $this->deleteUploadedFiles($user);
        $user->tokens()->delete();
        $user->delete();
        return $this->successResponse('success', 'User deleted successfully.', 200);
    }
    public function verify(Request $request)
    {
        $code = $request->input('code');
        $ipAddress = $request->ip();
        $verificationCode = Cache::get($ipAddress);
        $email = Cache::get($ipAddress . '_email');

        if ($verificationCode && $verificationCode == $code) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->email_verified_at = now();
                $user->save();
            }
            return response()->json(['message' => 'Email verified successfully']);
              Cache::forget($ipAddress);

        } else {
            return response()->json(['message' => 'Invalid verification code'], 404);
        }
    }
    public function resendVerificationCode(Request $request)
    {
        $ipAddress = $request->ip();
        $email = Cache::get($ipAddress . '_email');
        $code = Cache::get($ipAddress);

        if (!$code) {
            $newCode = VerificationCodeHelper::generate();
            Cache::put($ipAddress, $newCode, 3);
            Mail::to($email)->send(new verifymail($newCode));
            return $this->successResponse(null, 'Verification code expired, a new one has been sent', 200);
        } else {
            return $this->errorResponse('Verification code resent successfully',404);
        }
}

}
