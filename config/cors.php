<?php

/**
 * ============================================================
 * CORS - SHOTGUN-MOZ
 * ============================================================
 *
 * Configuração de Cross-Origin Resource Sharing.
 * Essencial para a app mobile (que vem de outro domínio).
 * ============================================================
 */

return [
    // Rotas onde CORS se aplica
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Métodos permitidos
    'allowed_methods' => ['*'],

    // ⚠️ Em produção, restringir aos domínios da tua app:
    // 'allowed_origins' => ['https://app.shotgun.mz', 'https://admin.shotgun.mz'],
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    // Headers permitidos
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Se a app envia cookies (SPA), mete true
    'supports_credentials' => false,
];