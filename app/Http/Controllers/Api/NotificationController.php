<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * POST /api/v1/notifications/token
     * Guarda o token FCM do dispositivo.
     */
    public function guardarToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'       => 'required|string',
            'device_name' => 'nullable|string|max:100',
            'platform'    => 'nullable|string|in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id'     => $user->id,
                'device_name' => $request->device_name ?? 'mobile',
                'platform'    => $request->platform ?? 'android',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token registado com sucesso.',
        ]);
    }
}