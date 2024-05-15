<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Enums\TokenAbility;
use Illuminate\Http\Request;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResposeTrait;
use App\Http\Traits\uploadFilesTrait;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Validation\ValidationException;

class UserAuthController extends Controller
{
    use ApiResposeTrait , uploadFilesTrait;

    public function register(RegisterUserRequest $request){

        try {
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            $photo_name = $request->file('profile_photo')->getClientOriginalName();
           $this->uploadProfilePhoto($request, 'profile_photo', 'profile_photos');
        }

        if ($request->hasFile('certificate')) {
            $file_name = $request->file('certificate')->getClientOriginalName();
           $this->uploadCertificate($request, 'certificate', 'certificate');
        }
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'phone_number'=>$data['phone_number'],
            'profile_photo' =>  $photo_name,
            'certificate' => $file_name,
        ]);

        event(new UserRegistered($user));

    } catch (ValidationException $e) {
        return $this->ApiResponse($e->validator->errors(),'Validation Error!',400);
    }
  return $this->ApiResponse( $user ,'user created.',201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'phone_number' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->ApiResponse('failed', $validator->errors(), 422); // Use appropriate error code (422 for validation errors)
        }

        if (auth()->attempt($request->only('email', 'password','phone_number'))) {
            $user = auth()->user();
        }

        if ($user) {
            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            return response()->json([
                'status' => 'success',
                'message' => 'Logged in successfully.',
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $refreshToken ? $refreshToken->plainTextToken : null // Include refresh token if generated
            ], 200);
        }

        else
        {
            return $this->ApiResponse('failed', 'Invalid credentials.', 401);
        }
}
    public function logout(Request $request)

    {
        $request->user('sanctum')->currentAccessToken()->delete();
        return $this->ApiResponse('success', 'Logged out successfully.', 200);
}

public function refreshToken(Request $request)
    {
        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        return response(['message' => "Token généré", 'token' => $accessToken->plainTextToken]);
    }
    }

