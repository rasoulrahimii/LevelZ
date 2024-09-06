<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterVerifyRequest;
use App\Http\Resources\UserResource;
use App\Models\Signup;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class RegisterVerifyController extends Controller
{
    public function __invoke(RegisterVerifyRequest $request): JsonResponse|UserResource
    {
        $countryCode = $request->input('country_code');
        $mobile = $request->input('mobile');
        $verificationCode = $request->integer('verification_code');

        $user = User::query()->where('country_code', $countryCode)->where('mobile', $mobile)->first();

        if ($user !== null) {
            return (new UserResource($user))->additional([
                'message' => 'You are already registered.',
            ])->response()->setStatusCode(409);
        }

        $almostUser = Signup::query()->where('country_code', $countryCode)->where('mobile', $mobile)->first();
        if ($almostUser === null) {
            throw new ModelNotFoundException('Almost user does not exist');
        }

        if ($almostUser->verification_code !== $verificationCode) {
            return response()->json([
                'message' => 'Invalid verification code',
            ], 400);
        }

        $almostUser->mobile_verified_at = now();
        $almostUser->save();

        return response()->json([
            'data' => [
                'user' => [],
            ],
            'registration_token' => $almostUser->registration_token,
        ]);
    }
}
