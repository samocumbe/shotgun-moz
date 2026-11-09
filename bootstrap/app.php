<?php

/**
 * ============================================================
 * BOOTSTRAP DA APLICAÇÃO - SHOTGUN-MOZ (Laravel 11)
 * ============================================================
 *
 * Configura:
 * - Rotas (web, api, console, health)
 * - Middleware global (Sanctum, CSRF, throttle)
 * - Tratamento de exceções
 * ============================================================
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ----------------------------------------------------
        // Sanctum: prepara os pedidos API para autenticação
        // stateful (SPA + tokens).
        // ----------------------------------------------------
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // ----------------------------------------------------
        // CSRF: excluído para webhooks externos (Meta)
        // ----------------------------------------------------
        $middleware->validateCsrfTokens(except: [
            'webhooks/whatsapp',
        ]);

        // ----------------------------------------------------
        // Rate limiting da API (por user autenticado)
        // ----------------------------------------------------
        // 60 requisições por minuto é razoável para mobile.
        // ----------------------------------------------------
        $middleware->throttleApi('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Podes registar handlers de exceção aqui no futuro.
    })->create();