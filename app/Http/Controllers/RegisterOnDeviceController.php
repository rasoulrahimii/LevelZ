<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterOnDeviceRequest;
use App\Http\Resources\UserResource;
use App\Models\Signup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class RegisterOnDeviceController extends Controller
{
    public function __invoke(RegisterOnDeviceRequest $request): JsonResponse|UserResource
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $registrationToken = $request->input('registration_token');

        $almostUser = Signup::query()->firstWhere('registration_token', $registrationToken);

        if ($almostUser === null) {
            return response()->json([
                'message' => 'You have not started the registration process yet.',
            ], 400);
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->country_code = $almostUser->country_code;
        $user->mobile = $almostUser->mobile;
        $user->pin = null;
        $user->token = Str::random(60);
        $user->mobile_verified_at = $almostUser->mobile_verified_at;
        $user->email_verified_at = null;
        $user->save();

        $almostUser->delete();

        return (new UserResource($user))->additional([
            'message' => 'You have successfully registered.',
        ]);
    }
}
