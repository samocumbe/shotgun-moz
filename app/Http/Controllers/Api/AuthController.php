<?php

/**
 * ============================================================
 * CONTROLLER: AuthController (API) - SHOTGUN-MOZ
 * ============================================================
 *
 * Autenticação de utilizadores e dispositivos via Laravel Sanctum.
 *
 * Endpoints:
 * - POST /api/auth/login       → login (público)
 * - POST /api/auth/register    → registo (público)
 * - POST /api/v1/auth/logout   → revoga token atual
 * - GET  /api/v1/auth/user     → dados do user autenticado
 * - PUT  /api/v1/auth/profile  → atualiza dados do perfil 🆕
 * - POST /api/v1/auth/refresh  → revoga + emite novo token
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dispositivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ============================================================
    // LOGIN
    // ============================================================

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:100',
            'device_type' => 'required|string|in:scanner,pos,supervisor,admin,mobile',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Erro de validação',
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas',
            ], 401);
        }

        $user = Auth::user();
        $user->tokens()->where('name', $request->device_name)->delete();
        $token = $user->createToken($request->device_name)->plainTextToken;

        $dispositivo = Dispositivo::updateOrCreate(
            ['nome' => $request->device_name, 'user_id' => $user->id],
            [
                'uuid'        => (string) Str::uuid(),
                'tipo'        => $request->device_type,
                'status'      => 'ativo',
                'ultimo_sync' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'token' => $token,
                'user'  => $this->formatarUser($user),
                'dispositivo' => [
                    'id'    => $dispositivo->id,
                    'uuid'  => $dispositivo->uuid,
                    'nome'  => $dispositivo->nome,
                    'tipo'  => $dispositivo->tipo,
                ],
            ],
            'message' => 'Login realizado com sucesso',
        ]);
    }

    // ============================================================
    // REGISTER
    // ============================================================

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'telefone' => 'nullable|string|max:20',
            'nuit'     => 'nullable|string|max:20',
            'device_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Erro de validação',
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'telefone' => $request->telefone,
            'nuit'     => $request->nuit,
            'role'     => User::ROLE_CLIENTE,
        ]);

        $deviceName = $request->input('device_name', 'mobile');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => [
                'token' => $token,
                'user'  => $this->formatarUser($user),
            ],
            'message' => 'Conta criada com sucesso',
        ], 201);
    }

    // ============================================================
    // LOGOUT
    // ============================================================

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]);
    }

    // ============================================================
    // USER
    // ============================================================

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'user'         => $this->formatarUser($user),
                'dispositivos' => $user->dispositivos()->where('status', 'ativo')->get(),
            ],
            'message' => 'Dados do utilizador obtidos com sucesso',
        ]);
    }

    // ============================================================
    // UPDATE PROFILE 🆕
    // ============================================================

    /**
     * PUT /api/v1/auth/profile
     * Atualiza os dados do perfil do utilizador autenticado.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'nuit'     => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Erro de validação',
            ], 422);
        }

        $user->update($request->only(['name', 'telefone', 'nuit', 'endereco']));

        return response()->json([
            'success' => true,
            'data'    => $this->formatarUser($user),
            'message' => 'Perfil atualizado com sucesso',
        ]);
    }

    // ============================================================
    // REFRESH
    // ============================================================

    public function refresh(Request $request)
    {
        $user = $request->user();
        $deviceName = $request->input('device_name', $user->currentAccessToken()->name);

        $user->currentAccessToken()->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => ['token' => $token],
            'message' => 'Token renovado',
        ]);
    }

    // ============================================================
    // AUXILIAR
    // ============================================================

    /**
     * Formata os dados do user para resposta da API.
     */
    protected function formatarUser(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'telefone'       => $user->telefone,
            'nuit'           => $user->nuit,
            'endereco'       => $user->endereco,
            'role'           => $user->role,
            'role_label'     => $user->role_name,
            'is_admin'       => $user->isAdmin(),
            'is_organizador' => $user->isOrganizador(),
            'organizacao_id' => $user->organizacao_id,
        ];
    }
}