{{--
    ============================================================
    VIEW: Carteira de Ingressos
    ============================================================
    Exibe todos os ingressos do usuário na carteira.
    
    Funcionalidades:
    1. Lista todos os ingressos do usuário em cards
    2. Cards com informações do ingresso (evento, categoria, status, código)
    3. Botão para visualizar detalhes do ingresso
    4. Botão para gerar QR Code
    5. Botão para transferir ingresso (FASE 6)
    6. Botão para solicitar reembolso (FASE 6)
    7. Resumo com cards estatísticos (Total, Ativos, Utilizados, Transferidos)
    8. Paginação dos ingressos
    9. Design moderno e responsivo
--}}

@extends('layouts.admin')

@section('title', 'Carteira de Ingressos - Shotgun MOZ')

@section('content')
{{-- ============================================================
    CABEÇALHO DA PÁGINA
    ============================================================ --}}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-wallet2 text-primary"></i> Carteira de Ingressos
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('transferencias.historico') }}" class="btn btn-sm btn-outline-primary me-2">
            <i class="bi bi-clock-history"></i> Histórico
        </a>
        <span class="text-muted">
            <i class="bi bi-ticket"></i> Total: <strong>{{ $carteira->total() }}</strong> ingressos
        </span>
    </div>
</div>

{{-- ============================================================
    CARDS DE RESUMO ESTATÍSTICO
    ============================================================ --}}
<div class="row mb-4">
    {{-- Total de Ingressos --}}
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card text-center">
            <div class="icon text-primary">
                <i class="bi bi-ticket"></i>
            </div>
            <div class="number">{{ $carteira->total() }}</div>
            <div class="label">Total de Ingressos</div>
        </div>
    </div>
    
    {{-- Ingressos Ativos --}}
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card text-center">
            <div class="icon text-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="number">{{ $carteira->where('status', 'ativo')->count() }}</div>
            <div class="label text-success">Ativos</div>
        </div>
    </div>
    
    {{-- Ingressos Utilizados --}}
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card text-center">
            <div class="icon text-secondary">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div class="number">{{ $carteira->where('status', 'utilizado')->count() }}</div>
            <div class="label text-secondary">Utilizados</div>
        </div>
    </div>
    
    {{-- Ingressos Transferidos --}}
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card text-center">
            <div class="icon text-warning">
                <i class="bi bi-arrow-right"></i>
            </div>
            <div class="number">{{ $carteira->where('status', 'transferido')->count() }}</div>
            <div class="label text-warning">Transferidos</div>
        </div>
    </div>
</div>

{{-- ============================================================
    LISTA DE INGRESSOS (CARDS)
    ============================================================ --}}
<div class="row">
    @forelse($carteira as $item)
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        {{-- Card do Ingresso --}}
        <div class="card h-100 shadow-sm hover-shadow-lg transition-all duration-300 border-0">
            
            {{-- ================================================
            CABEÇALHO DO CARD
            ================================================ --}}
            <div class="card-header d-flex justify-content-between align-items-center" 
                 style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-ticket text-primary"></i>
                    <strong class="text-truncate" style="max-width: 150px;" 
                            title="{{ $item->ingresso->evento->nome ?? 'N/A' }}">
                        {{ $item->ingresso->evento->nome ?? 'N/A' }}
                    </strong>
                </div>
                {{-- Status Badge --}}
                <span class="badge badge-status 
                    @if($item->status == 'ativo') badge-published
                    @elseif($item->status == 'utilizado') badge-ended
                    @elseif($item->status == 'transferido') badge-draft
                    @else badge-canceled @endif">
                    <i class="bi 
                        @if($item->status == 'ativo') bi-check-circle
                        @elseif($item->status == 'utilizado') bi-check2-circle
                        @elseif($item->status == 'transferido') bi-arrow-right
                        @else bi-x-circle @endif">
                    </i>
                    {{ ucfirst($item->status) }}
                </span>
            </div>
            
            {{-- ================================================
            CORPO DO CARD - DADOS DO INGRESSO
            ================================================ --}}
            <div class="card-body">
                {{-- Evento --}}
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-calendar-event"></i> Evento
                    </small>
                    <p class="mb-1 fw-semibold">{{ $item->ingresso->evento->nome ?? 'N/A' }}</p>
                </div>
                
                {{-- Categoria --}}
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-tag"></i> Categoria
                    </small>
                    <p class="mb-1">{{ $item->ingresso->categoria->nome ?? 'N/A' }}</p>
                </div>
                
                {{-- Código de Acesso --}}
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-qr-code"></i> Código
                    </small>
                    <p class="mb-1"><code class="bg-light p-1 rounded">{{ $item->codigo_acesso }}</code></p>
                </div>
                
                {{-- Data de Adição --}}
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-calendar-plus"></i> Adicionado em
                    </small>
                    <p class="mb-1">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</p>
                </div>
                
                {{-- Data de Utilização (se utilizada) --}}
                @if($item->data_utilizacao)
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-clock-history"></i> Utilizado em
                    </small>
                    <p class="mb-1 text-secondary">{{ \Carbon\Carbon::parse($item->data_utilizacao)->format('d/m/Y H:i') }}</p>
                </div>
                @endif

                {{-- Data de Ativação (se ativo) --}}
                @if($item->data_ativacao && $item->status == 'ativo')
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-check-circle"></i> Ativado em
                    </small>
                    <p class="mb-1 text-success">{{ \Carbon\Carbon::parse($item->data_ativacao)->format('d/m/Y H:i') }}</p>
                </div>
                @endif
            </div>
            
            {{-- ================================================
            RODAPÉ DO CARD - AÇÕES
            ================================================ --}}
            <div class="card-footer bg-transparent d-flex justify-content-between gap-2 flex-wrap">
                {{-- Visualizar Detalhes --}}
                <a href="{{ route('carteira.show', $item->id) }}" 
                   class="btn btn-sm btn-info flex-grow-1" 
                   title="Visualizar detalhes do ingresso"
                   data-bs-toggle="tooltip">
                    <i class="bi bi-eye"></i> Detalhes
                </a>
                
                {{-- QR Code --}}
                <a href="{{ route('carteira.qrcode', $item->id) }}" 
                   class="btn btn-sm btn-primary flex-grow-1" 
                   title="Gerar QR Code do ingresso"
                   data-bs-toggle="tooltip" target="_blank">
                    <i class="bi bi-qr-code"></i> QR Code
                </a>
                
                {{-- Transferir (apenas para ingressos ativos) --}}
                @if($item->status == 'ativo')
                <a href="{{ route('transferencias.create', $item->id) }}" 
                   class="btn btn-sm btn-warning flex-grow-1" 
                   title="Transferir ingresso para outra pessoa"
                   data-bs-toggle="tooltip">
                    <i class="bi bi-arrow-right"></i> Transferir
                </a>
                @endif
                
                {{-- Solicitar Reembolso (apenas para ingressos ativos) --}}
                @if($item->status == 'ativo')
                <a href="{{ route('reembolsos.create', $item->id) }}" 
                   class="btn btn-sm btn-danger flex-grow-1" 
                   title="Solicitar reembolso do ingresso"
                   data-bs-toggle="tooltip">
                    <i class="bi bi-arrow-return-left"></i> Reembolso
                </a>
                @endif
            </div>
        </div>
    </div>
    @empty
    {{-- ================================================
    MENSAGEM QUANDO NÃO HÁ INGRESSOS
    ================================================ --}}
    <div class="col-12">
        <div class="text-center py-5">
            <i class="bi bi-wallet2 text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">Sua carteira está vazia</h4>
            <p class="text-muted">Você ainda não possui nenhum ingresso na sua carteira.</p>
            <a href="{{ route('eventos.ativos') }}" class="btn btn-primary mt-2">
                <i class="bi bi-ticket"></i> Explorar Eventos
            </a>
        </div>
    </div>
    @endforelse
</div>

{{-- ============================================================
    PAGINAÇÃO
    ============================================================ --}}
@if($carteira->total() > 0)
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        <small class="text-muted">
            Mostrando {{ $carteira->firstItem() ?? 0 }} até {{ $carteira->lastItem() ?? 0 }} 
            de {{ $carteira->total() }} ingressos
        </small>
    </div>
    <div>
        {{ $carteira->links() }}
    </div>
</div>
@endif

{{-- ============================================================
    LEGENDA DE STATUS
    ============================================================ --}}
<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-light border shadow-sm">
            <i class="bi bi-info-circle text-primary"></i>
            <strong>Legenda de Status:</strong>
            <span class="ms-3 text-success">
                <i class="bi bi-check-circle"></i> Ativo - Disponível para uso
            </span>
            <span class="ms-3 text-secondary">
                <i class="bi bi-check2-circle"></i> Utilizado - Já foi usado no evento
            </span>
            <span class="ms-3 text-warning">
                <i class="bi bi-arrow-right"></i> Transferido - Enviado para outra pessoa
            </span>
            <span class="ms-3 text-danger">
                <i class="bi bi-x-circle"></i> Cancelado - Não pode mais ser usado
            </span>
        </div>
    </div>
</div>
@endsection

{{-- ============================================================
    ESTILOS ADICIONAIS
    ============================================================ --}}
@push('styles')
<style>
    /* ========================================================
       ANIMAÇÕES E TRANSIÇÕES
       ======================================================== */
    .transition-all {
        transition: all 0.3s ease;
    }
    
    .hover-shadow-lg:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-4px);
    }
    
    /* ========================================================
       BADGES DE STATUS
       ======================================================== */
    .card-header .badge-status {
        font-size: 0.65rem;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .card-header .badge-status i {
        font-size: 0.6rem;
        margin-right: 4px;
    }
    
    /* ========================================================
       BOTÕES DO RODAPÉ
       ======================================================== */
    .card-footer .btn {
        font-size: 0.75rem;
        padding: 0.3rem 0.5rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .card-footer .btn:hover {
        transform: scale(1.02);
    }
    
    .card-footer .btn-info {
        background-color: #0dcaf0;
        border-color: #0dcaf0;
        color: #000;
    }
    
    .card-footer .btn-info:hover {
        background-color: #31d2f2;
        border-color: #25cff2;
    }
    
    /* ========================================================
       CARDS ESTATÍSTICOS
       ======================================================== */
    .stat-card {
        padding: 20px 15px;
        border-radius: 12px;
        background: #fff;
        box-shadow: var(--card-shadow, 0 2px 12px rgba(0,0,0,0.08));
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0, 0, 0, 0.03);
        height: 100%;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }
    
    .stat-card .icon {
        font-size: 2rem;
        margin-bottom: 8px;
    }
    
    .stat-card .number {
        font-size: 2rem;
        font-weight: 700;
        color: #1a2332;
        line-height: 1.2;
    }
    
    .stat-card .label {
        color: #7f8c8d;
        font-size: 0.85rem;
        font-weight: 500;
        margin-top: 4px;
    }
    
    /* ========================================================
       RESPONSIVIDADE
       ======================================================== */
    @media (max-width: 576px) {
        /* Cards em coluna no mobile */
        .card-footer {
            flex-direction: column;
            gap: 6px;
        }
        .card-footer .btn {
            width: 100%;
            font-size: 0.7rem;
        }
        .card-header .text-truncate {
            max-width: 100px !important;
            font-size: 0.85rem;
        }
        .stat-card .number {
            font-size: 1.5rem;
        }
        .stat-card .icon {
            font-size: 1.5rem;
        }
        .card-body p {
            font-size: 0.85rem;
        }
        .card-body small {
            font-size: 0.7rem;
        }
    }
    
    @media (min-width: 577px) and (max-width: 992px) {
        .card-footer {
            flex-wrap: wrap;
            gap: 4px;
        }
        .card-footer .btn {
            flex: 1 1 45%;
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }
    }
    
    @media (min-width: 993px) and (max-width: 1200px) {
        .card-footer .btn {
            font-size: 0.65rem;
            padding: 0.25rem 0.3rem;
        }
    }
</style>
@endpush

{{-- ============================================================
    SCRIPTS ADICIONAIS
    ============================================================ --}}
@push('scripts')
<script>
    /**
     * ============================================================
     * INICIALIZAR TOOLTIPS DO BOOTSTRAP
     * ============================================================
     * Ativa os tooltips para todos os elementos com data-bs-toggle="tooltip"
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                placement: 'top',
                trigger: 'hover'
            });
        });
        
        // Atualizar tooltips após carregamento dinâmico
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('message.processed', () => {
                var newTooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                newTooltips.forEach(function(el) {
                    if (!el._tippy) {
                        new bootstrap.Tooltip(el, {
                            placement: 'top',
                            trigger: 'hover'
                        });
                    }
                });
            });
        }
    });
</script>
@endpush