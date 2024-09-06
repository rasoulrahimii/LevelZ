<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SetPinRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SetPinController extends Controller
{
    public function __invoke(SetPinRequest $request): JsonResponse|UserResource
    {
        $token = $request->input('token');
        $pin = $request->input('pin');

        $user = User::query()->firstWhere('token', $token);

        if ($user === null) {
            return response()->json([
                'message' => 'Invalid registration token',
            ], 400);
        }

        $user->pin = bcrypt($pin);
        $user->save();

        return (new UserResource($user))->additional([
            'message' => 'You have successfully set your PIN.',
        ]);
    }
}
