<?php

/**
 * ============================================================
 * API Routes - SHOTGUN-MOZ
 * ============================================================
 *
 * API REST consumida pela app mobile e dispositivos
 * (scanners/POS). Autenticação via Laravel Sanctum.
 *
 * Estrutura:
 * - /api/teste            → health check (público)
 * - /api/auth/login       → autentica dispositivo/user (público)
 * - /api/auth/register    → regista user comum (público)
 * - /api/v1/...           → todos os endpoints protegidos
 *
 * Ordem das rotas:
 * - Rotas específicas (ex: /scanner/eventos/{id}/ingressos) DEVEM
 *   vir ANTES das genéricas com parâmetro (ex: /scanner/{id}).
 * ============================================================
 */

use Illuminate\Support\Facades\Route;

// --- Controllers de Autenticação ---
use App\Http\Controllers\Api\AuthController;

// --- Controllers de Recursos ---
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\ScannerController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\CarteiraController;
use App\Http\Controllers\Api\PedidoApiController;

// --- Controllers de Notificações 🆕 ---
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| ROTA PÚBLICA DE TESTE (health check)
|--------------------------------------------------------------------------
*/
Route::get('/teste', function () {
    return response()->json([
        'message' => 'API Shotgun-MOZ funcionando!',
        'versao'  => 'v1',
        'hora'    => now()->toIso8601String(),
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO (PÚBLICA)
|--------------------------------------------------------------------------
| Rate limiting para proteger contra força bruta:
| - login:    5 tentativas por minuto
| - register: 3 registos por minuto
*/
Route::prefix('auth')->group(function () {
    Route::post('/login',    [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:3,1');
});

/*
|--------------------------------------------------------------------------
| API v1 (PROTEGIDA COM SANCTUM)
|--------------------------------------------------------------------------
| Todas as rotas dentro deste grupo requerem:
| - Header: Authorization: Bearer {token}
*/
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // ----------------------------------------------------------
    // Autenticação e Perfil
    // ----------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('/logout',  [AuthController::class, 'logout']);
        Route::get('/user',     [AuthController::class, 'user']);
        Route::put('/profile',  [AuthController::class, 'updateProfile']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // ----------------------------------------------------------
    // Eventos
    // ----------------------------------------------------------
    Route::prefix('eventos')->group(function () {
        Route::get('/',     [EventoController::class, 'index']);
        Route::get('/{id}', [EventoController::class, 'show']);
    });

    // ----------------------------------------------------------
    // Scanner / Validação QR
    // ----------------------------------------------------------
    // ⚠️ Rotas com path específico DEVEM vir antes das genéricas
    Route::prefix('scanner')->group(function () {
        Route::post('/validar',                     [ScannerController::class, 'validar']);
        Route::get('/eventos/{eventoId}/ingressos', [ScannerController::class, 'ingressos']);
    });

    // ----------------------------------------------------------
    // Sincronização Offline
    // ----------------------------------------------------------
    Route::prefix('sync')->group(function () {
        Route::post('/checkins',  [SyncController::class, 'syncCheckins']);
        Route::get('/pendentes',  [SyncController::class, 'pendentes']);
        Route::post('/confirmar', [SyncController::class, 'confirmar']);
    });

    // ----------------------------------------------------------
    // Carteira do utilizador (saldo + ingressos digitais)
    // ----------------------------------------------------------
    Route::prefix('carteira')->group(function () {
        Route::get('/',          [CarteiraController::class, 'index']);
        Route::get('/ingressos', [CarteiraController::class, 'ingressos']);
        Route::get('/saldo',     [CarteiraController::class, 'saldo']);
    });

    // ----------------------------------------------------------
    // Compras (Checkout)
    // ----------------------------------------------------------
    // Endpoint chamado quando o utilizador clica "Comprar" na app.
    Route::prefix('pedidos')->group(function () {
        Route::post('/checkout', [PedidoApiController::class, 'checkout']);
        Route::get('/',          [PedidoApiController::class, 'index']);
    });

    // ----------------------------------------------------------
    // Notificações Push 🆕
    // ----------------------------------------------------------
    // Recebe o token FCM do dispositivo após o login.
    Route::prefix('notifications')->group(function () {
        Route::post('/token', [NotificationController::class, 'guardarToken']);
    });

});