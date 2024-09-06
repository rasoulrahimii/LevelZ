<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterMobileRequest;
use App\Models\Signup;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class RegisterMobileController extends Controller
{
    public function __invoke(RegisterMobileRequest $request): JsonResponse
    {
        $countryCode = $request->input('country_code');
        $mobile = $request->input('mobile');

        $verificationCode = rand(100000, 999999);
        $registrationToken = Str::random(60);

        $signup = Signup::query()->where('country_code', $countryCode)->where('mobile', $mobile)->first();

        // Throttle repetitive requests
        if ($signup !== null && $signup->updated_at->addMinutes(2)->greaterThan(now())) {
            return response()->json([
                'message' => 'At least 2 minutes must pass since your last successful attempt.',
            ], 400);
        }

        Signup::query()->updateOrCreate(
            ['country_code' => $countryCode, 'mobile' => $mobile],
            ['verification_code' => $verificationCode, 'registration_token' => $registrationToken]
        );

        return response()->json([
            'message' => 'Your request has been fulfilled successfully.',
            'exists' => false,
            'mobile_verified' => false,
            'mobile_verification_code' => $verificationCode,
        ]);
    }
}
