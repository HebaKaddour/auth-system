<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Enums\TokenAbility;
use Illuminate\Http\Request;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;


class UserAuthController extends Controller
{

    public function register(RegisterUserRequest $request){


        $data = $request->validated();

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->phone_number = $data['phone_number'];


        if ($request->hasFile('profile_photo')) {
        $imageName = $request->file('profile_photo')->getClientOriginalName();
        $this->uploadImage($request->file('profile_photo'), 'photos', 'photos');
        $user->profile_photo =$imageName;
        }

        if ($request->hasFile('certificate')) {
        $fileName = $request->file('certificate')->getClientOriginalName();
         $this->uploadfile($request->file('certificate'), 'certificate', 'certificates');
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
        if ($user) {
            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            return response()->json([
                'status' => 'success',
                'message' => 'Logged in successfully.',
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $refreshToken ? $refreshToken->plainTextToken : null
            ], 200);

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
        $data = [
            'refreshToken' => $refreshToken->plainTextToken,
        ];
        return $this->successResponse($data, 'New refresh token created.', 200);
    }

 }

