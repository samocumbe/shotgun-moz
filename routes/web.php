<?php

/*
|--------------------------------------------------------------------------
| Web Routes - SHOTGUN-MOZ
|--------------------------------------------------------------------------
|
| Aqui estão todas as rotas da aplicação para interface web.
| As rotas são divididas em:
| - Rotas públicas (acesso livre)
| - Rotas autenticadas (requer login)
| - Rotas administrativas (requer login + prefixo /admin)
|
| Todas as rotas com autenticação são protegidas pelo middleware 'auth'.
|
| ⚠️ IMPORTANTE: a ORDEM de declaração das rotas importa!
|    Rotas específicas (ex: /sms/templates, /whatsapp/massa) DEVEM vir
|    ANTES das rotas genéricas com parâmetro (ex: /sms/{id}, /whatsapp/{id}),
|    caso contrário o Laravel interpreta a palavra como um ID e devolve 404.
|
| MÓDULOS (numerados para fácil referência):
| 01. Locais
| 02. Eventos (com Multi-Eventos e Templates)
| 03. Categorias de Ingresso
| 04. Ingressos
| 05. Check-ins
| 06. Dispositivos
| 07. Vendas
| 08. Pedidos
| 09. Pagamentos
| 10. Descontos
| 11. Promoters
| 12. Relatórios
| 13. Notificações
| 14. Carteira
| 15. Transferências
| 16. Reembolsos
| 17. Usuários
| 18. Organizações
| 19. Auditoria
| 20. SMS + Templates + Envio em Massa
| 21. WhatsApp + Templates + Envio em Massa
|--------------------------------------------------------------------------
*/

// ======================================================================
// IMPORTAÇÃO DOS CONTROLLERS
// ======================================================================

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\CategoriaIngressoController;
use App\Http\Controllers\IngressoController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\DispositivoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\CupomController;
use App\Http\Controllers\PromoterController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\CarteiraController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\ReembolsoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizacaoController;
use App\Http\Controllers\AuditoriaController;

// Módulo SMS
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\SmsMassaController;

// Módulo WhatsApp
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppTemplateController;
use App\Http\Controllers\WhatsAppMassaController;
use App\Http\Controllers\WhatsAppWebhookController;

use Illuminate\Support\Facades\Route;


// ======================================================================
// ROTAS PÚBLICAS
// ======================================================================

/**
 * Rota principal - Página inicial (Welcome)
 * Acessível por qualquer visitante
 */
Route::get('/', function () {
    return view('welcome');
})->name('home');

/**
 * Rotas geradas automaticamente pelo Laravel Breeze
 * Gerenciam: Login, Register, Logout, Reset Password, Verificação de Email
 */
require __DIR__.'/auth.php';


// ======================================================================
// ROTAS AUTENTICADAS (REQUEREM LOGIN)
// ======================================================================

Route::middleware(['auth'])->group(function () {

    // ==================================================================
    // DASHBOARD
    // ==================================================================

    /**
     * Dashboard principal (acessado via /dashboard)
     * Requer: utilizador autenticado e verificado por email
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');

    /**
     * Dashboard do administrador (acessado via /admin/dashboard)
     */
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');


    // ==================================================================
    // ROTAS ADMINISTRATIVAS (PREFIXO /admin)
    // ==================================================================

    Route::prefix('admin')->group(function () {

        // --------------------------------------------------------------
        // 1. LOCAIS
        // --------------------------------------------------------------
        Route::resource('locais', LocalController::class);
        Route::get('locais/{id}/eventos', [LocalController::class, 'eventos'])->name('locais.eventos');

        // --------------------------------------------------------------
        // 2. EVENTOS (com Multi-Eventos e Templates)
        // --------------------------------------------------------------
        Route::get('eventos/templates', function () {
            $templates = App\Models\Evento::where('is_template', true)
                                          ->with(['local', 'categoriasIngressos', 'organizacao'])
                                          ->paginate(12);
            return view('admin.eventos.templates', compact('templates'));
        })->name('eventos.templates');

        Route::post('eventos/{id}/duplicar', [EventoController::class, 'duplicar'])->name('eventos.duplicar');
        Route::post('eventos/{id}/template', [EventoController::class, 'salvarTemplate'])->name('eventos.template');
        Route::get('eventos/criar-template/{id}', [EventoController::class, 'criarDoTemplate'])->name('eventos.criar-template');
        Route::post('eventos/{id}/gerar-recorrentes', [EventoController::class, 'gerarRecorrentes'])->name('eventos.gerar-recorrentes');
        Route::post('eventos/{id}/desativar-template', [EventoController::class, 'desativarTemplate'])->name('eventos.desativar-template');
        Route::post('eventos/{id}/cancelar', [EventoController::class, 'cancelar'])->name('eventos.cancelar');
        Route::post('eventos/{id}/restaurar', [EventoController::class, 'restaurar'])->name('eventos.restaurar');

        Route::resource('eventos', EventoController::class);

        Route::post('eventos/{id}/publicar', [EventoController::class, 'publicar'])->name('eventos.publicar');
        Route::get('eventos/{id}/estatisticas', [EventoController::class, 'estatisticas'])->name('eventos.estatisticas');
        Route::get('eventos/ativos', [EventoController::class, 'ativos'])->name('eventos.ativos');

        // --------------------------------------------------------------
        // 3. CATEGORIAS DE INGRESSO
        // --------------------------------------------------------------
        Route::prefix('eventos/{eventoId}/categorias')->group(function () {
            Route::get('/', [CategoriaIngressoController::class, 'index'])->name('categorias.index');
            Route::get('/create', [CategoriaIngressoController::class, 'create'])->name('categorias.create');
            Route::post('/', [CategoriaIngressoController::class, 'store'])->name('categorias.store');
            Route::get('/{id}', [CategoriaIngressoController::class, 'show'])->name('categorias.show');
            Route::get('/{id}/edit', [CategoriaIngressoController::class, 'edit'])->name('categorias.edit');
            Route::put('/{id}', [CategoriaIngressoController::class, 'update'])->name('categorias.update');
            Route::delete('/{id}', [CategoriaIngressoController::class, 'destroy'])->name('categorias.destroy');
        });

        // --------------------------------------------------------------
        // 4. INGRESSOS
        // --------------------------------------------------------------
        Route::get('ingressos', [IngressoController::class, 'geral'])->name('ingressos.geral');

        Route::prefix('eventos/{eventoId}/ingressos')->group(function () {
            Route::get('/', [IngressoController::class, 'index'])->name('ingressos.index');
            Route::post('/', [IngressoController::class, 'store'])->name('ingressos.store');
            Route::get('/{id}', [IngressoController::class, 'show'])->name('ingressos.show');
            Route::post('/{id}/cancelar', [IngressoController::class, 'cancelar'])->name('ingressos.cancelar');
            Route::get('/{id}/qrcode', [IngressoController::class, 'gerarQRCode'])->name('ingressos.qrcode');
            Route::post('/validar', [IngressoController::class, 'validar'])->name('ingressos.validar');
        });

        // --------------------------------------------------------------
        // 5. CHECKINS
        // --------------------------------------------------------------
        Route::get('checkins', [CheckinController::class, 'indexWeb'])->name('checkins.index');
        Route::get('checkins/{id}', [CheckinController::class, 'show'])->name('checkins.show');

        Route::prefix('eventos/{eventoId}/checkins')->group(function () {
            Route::get('/', [CheckinController::class, 'index'])->name('checkins.api.index');
            Route::post('/', [CheckinController::class, 'store'])->name('checkins.store');
            Route::post('/sincronizar', [CheckinController::class, 'sincronizar'])->name('checkins.sincronizar');
            Route::get('/pendentes', [CheckinController::class, 'pendentes'])->name('checkins.pendentes');
            Route::get('/estatisticas', [CheckinController::class, 'estatisticas'])->name('checkins.estatisticas');
        });

        // --------------------------------------------------------------
        // 6. DISPOSITIVOS
        // --------------------------------------------------------------
        Route::resource('dispositivos', DispositivoController::class);
        Route::post('dispositivos/{id}/sincronizar', [DispositivoController::class, 'sincronizar'])->name('dispositivos.sincronizar');

        // --------------------------------------------------------------
        // 7. VENDAS
        // --------------------------------------------------------------
        Route::prefix('vendas')->group(function () {
            Route::get('/', [VendaController::class, 'index'])->name('vendas.index');
            Route::get('/create', [VendaController::class, 'create'])->name('vendas.create');
            Route::post('/', [VendaController::class, 'store'])->name('vendas.store');
            Route::get('/{id}', [VendaController::class, 'show'])->name('vendas.show');
            Route::post('/{id}/cancelar', [VendaController::class, 'cancelar'])->name('vendas.cancelar');
            Route::get('/eventos/{eventoId}/categorias', [VendaController::class, 'getCategorias'])->name('vendas.categorias');
            Route::get('/eventos/{eventoId}/estatisticas', [VendaController::class, 'estatisticas'])->name('vendas.estatisticas');
            Route::get('/eventos/{eventoId}/pdf', [VendaController::class, 'relatorioPdf'])->name('vendas.pdf');
            Route::get('/{id}/recibo', [VendaController::class, 'recibo'])->name('vendas.recibo');
            Route::post('/{id}/enviar-email', [VendaController::class, 'enviarEmail'])->name('vendas.enviar-email');
            Route::get('/{id}/ingressos', [VendaController::class, 'ingressosJson'])->name('vendas.ingressos-json');
        });

        // --------------------------------------------------------------
        // 8. PEDIDOS
        // --------------------------------------------------------------
        Route::prefix('pedidos')->group(function () {
            Route::get('/', [PedidoController::class, 'index'])->name('pedidos.index');
            Route::get('/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
            Route::put('/{id}/status', [PedidoController::class, 'updateStatus'])->name('pedidos.status');
            Route::get('/participante/{participanteId}', [PedidoController::class, 'porParticipante'])->name('pedidos.participante');
        });

        // --------------------------------------------------------------
        // 9. PAGAMENTOS
        // --------------------------------------------------------------
        Route::prefix('pagamentos')->group(function () {
            Route::get('/', [PagamentoController::class, 'index'])->name('pagamentos.index');
            Route::post('/', [PagamentoController::class, 'store'])->name('pagamentos.store');
            Route::get('/{id}', [PagamentoController::class, 'show'])->name('pagamentos.show');
            Route::post('/{id}/confirmar', [PagamentoController::class, 'confirmar'])->name('pagamentos.confirmar');
        });

        // --------------------------------------------------------------
        // 10. DESCONTOS
        // --------------------------------------------------------------
        Route::resource('descontos', CupomController::class);
        Route::post('/descontos/validar', [CupomController::class, 'validar'])->name('descontos.validar');
        Route::post('/descontos/aplicar', [CupomController::class, 'aplicar'])->name('descontos.aplicar');

        // --------------------------------------------------------------
        // 11. PROMOTERS
        // --------------------------------------------------------------
        Route::resource('promoters', PromoterController::class);
        Route::get('promoters/{id}/comissoes', [PromoterController::class, 'comissoes'])->name('promoters.comissoes');
        Route::post('promoters/comissoes/{id}/pagar', [PromoterController::class, 'pagarComissao'])->name('promoters.comissoes.pagar');

        // --------------------------------------------------------------
        // 12. RELATÓRIOS
        // --------------------------------------------------------------
        Route::prefix('relatorios')->group(function () {
            Route::get('/', [RelatorioController::class, 'index'])->name('relatorios.index');
            Route::get('vendas/{eventoId}', [RelatorioController::class, 'vendas'])->name('relatorios.vendas');
            Route::get('checkins/{eventoId}', [RelatorioController::class, 'checkins'])->name('relatorios.checkins');
            Route::get('financeiro/{eventoId}', [RelatorioController::class, 'financeiro'])->name('relatorios.financeiro');
            Route::get('promoters', [RelatorioController::class, 'promoters'])->name('relatorios.promoters');
            Route::get('promoters/{id}/detalhes', [RelatorioController::class, 'promoterDetalhes'])->name('relatorios.promoter-detalhes');
        });

        // --------------------------------------------------------------
        // 13. NOTIFICAÇÕES
        // --------------------------------------------------------------
        Route::prefix('notificacoes')->group(function () {
            Route::get('/', [NotificacaoController::class, 'index'])->name('notificacoes.index');
            Route::post('/{id}/marcar-lida', [NotificacaoController::class, 'marcarLida'])->name('notificacoes.marcar-lida');
            Route::post('/marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasLidas'])->name('notificacoes.marcar-todas-lidas');
            Route::delete('/{id}', [NotificacaoController::class, 'destroy'])->name('notificacoes.destroy');
        });

        // --------------------------------------------------------------
        // 14. CARTEIRA
        // --------------------------------------------------------------
        Route::prefix('carteira')->group(function () {
            Route::get('/', [CarteiraController::class, 'index'])->name('carteira.index');
            Route::post('/', [CarteiraController::class, 'store'])->name('carteira.store');
            Route::get('/{id}', [CarteiraController::class, 'show'])->name('carteira.show');
            Route::get('/{id}/qrcode', [CarteiraController::class, 'qrcode'])->name('carteira.qrcode');
        });

        // --------------------------------------------------------------
        // 15. TRANSFERÊNCIAS
        // --------------------------------------------------------------
        Route::prefix('transferencias')->group(function () {
            Route::get('criar/{carteiraId}', [TransferenciaController::class, 'create'])->name('transferencias.create');
            Route::post('/', [TransferenciaController::class, 'store'])->name('transferencias.store');
            Route::get('confirmar/{id}', [TransferenciaController::class, 'confirmar'])->name('transferencias.confirmar');
            Route::post('confirmar/{id}', [TransferenciaController::class, 'confirmarStore'])->name('transferencias.confirmar-store');
            Route::get('historico', [TransferenciaController::class, 'historico'])->name('transferencias.historico');
        });

        // --------------------------------------------------------------
        // 16. REEMBOLSOS (ORDEM CORRIGIDA: admin vem primeiro)
        // --------------------------------------------------------------
        Route::prefix('reembolsos')->group(function () {
            
            // 1. Rota específica para admin (TEM DE VIR PRIMEIRO)
            Route::prefix('admin')->group(function () {
                Route::get('/', [ReembolsoController::class, 'adminIndex'])->name('admin.reembolsos');
                Route::post('/{id}/aprovar', [ReembolsoController::class, 'adminAprovar'])->name('admin.reembolsos.aprovar');
                Route::post('/{id}/rejeitar', [ReembolsoController::class, 'adminRejeitar'])->name('admin.reembolsos.rejeitar');
                Route::post('/{id}/processar', [ReembolsoController::class, 'adminProcessar'])->name('admin.reembolsos.processar');
            });

            // 2. Rotas genéricas (VÊM DEPOIS)
            Route::get('/', [ReembolsoController::class, 'index'])->name('reembolsos.index');
            Route::get('criar/{carteiraId}', [ReembolsoController::class, 'create'])->name('reembolsos.create');
            Route::post('/', [ReembolsoController::class, 'store'])->name('reembolsos.store');
            Route::get('/{id}', [ReembolsoController::class, 'show'])->name('reembolsos.show');
        });

        // --------------------------------------------------------------
        // 17. USUÁRIOS
        // --------------------------------------------------------------
        Route::resource('users', UserController::class);

        // --------------------------------------------------------------
        // 18. ORGANIZAÇÕES (Multi-Organizador)
        // --------------------------------------------------------------
        Route::resource('organizacoes', OrganizacaoController::class);

        // --------------------------------------------------------------
        // 19. AUDITORIA
        // --------------------------------------------------------------
        Route::prefix('auditoria')->group(function () {
            Route::get('/', [AuditoriaController::class, 'index'])->name('auditoria.index');
            Route::get('/exportar', [AuditoriaController::class, 'exportar'])->name('auditoria.exportar');
            Route::post('/limpar', [AuditoriaController::class, 'limpar'])->name('auditoria.limpar');
            Route::get('/{id}', [AuditoriaController::class, 'show'])->name('auditoria.show');
        });

        // ==============================================================
        // 20. SMS + TEMPLATES + ENVIO EM MASSA
        // ==============================================================
        Route::prefix('sms')->group(function () {
            Route::prefix('templates')->group(function () {
                Route::get('/', [SmsTemplateController::class, 'index'])->name('sms.templates.index');
                Route::get('/create', [SmsTemplateController::class, 'create'])->name('sms.templates.create');
                Route::post('/', [SmsTemplateController::class, 'store'])->name('sms.templates.store');
                Route::get('/{id}', [SmsTemplateController::class, 'show'])->name('sms.templates.show');
                Route::get('/{id}/edit', [SmsTemplateController::class, 'edit'])->name('sms.templates.edit');
                Route::put('/{id}', [SmsTemplateController::class, 'update'])->name('sms.templates.update');
                Route::delete('/{id}', [SmsTemplateController::class, 'destroy'])->name('sms.templates.destroy');
                Route::post('/{id}/toggle-ativo', [SmsTemplateController::class, 'toggleAtivo'])->name('sms.templates.toggle-ativo');
                Route::get('/{id}/render-json', [SmsTemplateController::class, 'renderJson'])->name('sms.templates.render-json');
            });

            Route::prefix('massa')->group(function () {
                Route::get('/', [SmsMassaController::class, 'index'])->name('sms.massa.index');
                Route::get('/evento/{eventoId}', [SmsMassaController::class, 'criar'])->name('sms.massa.criar');
                Route::post('/preview', [SmsMassaController::class, 'preview'])->name('sms.massa.preview');
                Route::post('/enviar', [SmsMassaController::class, 'enviar'])->name('sms.massa.enviar');
                Route::get('/resultado/{loteId}', [SmsMassaController::class, 'resultado'])->name('sms.massa.resultado');
            });

            Route::get('/', [SmsController::class, 'index'])->name('sms.index');
            Route::get('/create', [SmsController::class, 'create'])->name('sms.create');
            Route::post('/', [SmsController::class, 'store'])->name('sms.store');
            Route::get('/{id}', [SmsController::class, 'show'])->name('sms.show');
            Route::get('/{id}/json', [SmsController::class, 'showJson'])->name('sms.show-json');
            Route::post('/{id}/reenviar', [SmsController::class, 'reenviar'])->name('sms.reenviar');
        });

        // ==============================================================
        // 21. WHATSAPP + TEMPLATES + ENVIO EM MASSA
        // ==============================================================
        Route::prefix('whatsapp')->group(function () {
            Route::prefix('templates')->group(function () {
                Route::get('/', [WhatsAppTemplateController::class, 'index'])->name('whatsapp.templates.index');
                Route::get('/create', [WhatsAppTemplateController::class, 'create'])->name('whatsapp.templates.create');
                Route::post('/', [WhatsAppTemplateController::class, 'store'])->name('whatsapp.templates.store');
                Route::get('/{id}', [WhatsAppTemplateController::class, 'show'])->name('whatsapp.templates.show');
                Route::get('/{id}/edit', [WhatsAppTemplateController::class, 'edit'])->name('whatsapp.templates.edit');
                Route::put('/{id}', [WhatsAppTemplateController::class, 'update'])->name('whatsapp.templates.update');
                Route::delete('/{id}', [WhatsAppTemplateController::class, 'destroy'])->name('whatsapp.templates.destroy');
                Route::post('/{id}/toggle-ativo', [WhatsAppTemplateController::class, 'toggleAtivo'])->name('whatsapp.templates.toggle-ativo');
                Route::get('/{id}/preview-json', [WhatsAppTemplateController::class, 'previewJson'])->name('whatsapp.templates.preview-json');
            });

            Route::prefix('massa')->group(function () {
                Route::get('/',                       [WhatsAppMassaController::class, 'index'])->name('whatsapp.massa.index');
                Route::get('/evento/{eventoId}',      [WhatsAppMassaController::class, 'criar'])->name('whatsapp.massa.criar');
                Route::post('/preview',               [WhatsAppMassaController::class, 'preview'])->name('whatsapp.massa.preview');
                Route::post('/enviar',                [WhatsAppMassaController::class, 'enviar'])->name('whatsapp.massa.enviar');
                Route::get('/resultado/{loteId}',     [WhatsAppMassaController::class, 'resultado'])->name('whatsapp.massa.resultado');
            });

            Route::get('/', [WhatsAppController::class, 'index'])->name('whatsapp.index');
            Route::get('/create', [WhatsAppController::class, 'create'])->name('whatsapp.create');
            Route::post('/', [WhatsAppController::class, 'store'])->name('whatsapp.store');
            Route::get('/{id}', [WhatsAppController::class, 'show'])->name('whatsapp.show');
            Route::get('/{id}/json', [WhatsAppController::class, 'showJson'])->name('whatsapp.show-json');
            Route::post('/{id}/reenviar', [WhatsAppController::class, 'reenviar'])->name('whatsapp.reenviar');
        });

    }); // ✅ Fim do prefixo /admin


    // ==================================================================
    // ROTAS DE PERFIL DO UTILIZADOR
    // ==================================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

}); // ✅ Fim do middleware auth

// ======================================================================
// WEBHOOKS (PÚBLICO - SEM AUTH)
// ======================================================================
Route::prefix('webhooks')->group(function () {
    Route::get('/whatsapp',  [WhatsAppWebhookController::class, 'verificar'])
        ->name('webhooks.whatsapp.verificar');

    Route::post('/whatsapp', [WhatsAppWebhookController::class, 'receber'])
        ->name('webhooks.whatsapp.receber');
});