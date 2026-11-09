{{--
    ============================================================
    VIEW: Detalhes do Ingresso na Carteira
    ============================================================
    Exibe todos os detalhes de um ingresso específico na carteira.
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Ingresso - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-ticket text-primary"></i> Detalhes do Ingresso</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('carteira.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('carteira.qrcode', $item->id) }}" class="btn btn-sm btn-primary ms-2">
            <i class="bi bi-qr-code"></i> QR Code
        </a>
        @if($item->status == 'ativo')
        <a href="{{ route('transferencias.create', $item->id) }}" class="btn btn-sm btn-warning ms-2">
            <i class="bi bi-arrow-right"></i> Transferir
        </a>
        <a href="{{ route('reembolsos.create', $item->id) }}" class="btn btn-sm btn-danger ms-2">
            <i class="bi bi-arrow-return-left"></i> Reembolso
        </a>
        @endif
    </div>
</div>

<div class="row">
    {{-- Coluna Principal: Detalhes do Ingresso --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Ingresso</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Evento:</strong>
                        <p class="mt-1">{{ $item->ingresso->evento->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p class="mt-1">
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
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Categoria:</strong>
                        <p class="mt-1">{{ $item->ingresso->categoria->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Preço Pago:</strong>
                        <p class="mt-1"><strong>{{ number_format($item->ingresso->preco_pago ?? 0, 2, ',', '.') }} MZN</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Código de Acesso:</strong>
                        <p class="mt-1"><code class="bg-light p-2 rounded">{{ $item->codigo_acesso }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <strong>QR Code:</strong>
                        <p class="mt-1"><code>{{ $item->ingresso->qr_code ?? 'N/A' }}</code></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Adicionado em:</strong>
                        <p class="mt-1">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Ativado em:</strong>
                        <p class="mt-1">
                            @if($item->data_ativacao)
                                {{ \Carbon\Carbon::parse($item->data_ativacao)->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">Não ativado</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if($item->data_utilizacao)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Utilizado em:</strong>
                        <p class="mt-1 text-secondary">{{ \Carbon\Carbon::parse($item->data_utilizacao)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif

                @if($item->ingresso->participante)
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Participante:</strong>
                        <p class="mt-1">{{ $item->ingresso->participante->nome_completo ?? 'N/A' }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar: Resumo e Ações --}}
    <div class="col-md-4">
        {{-- Resumo do Evento --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Evento</h5>
            </div>
            <div class="card-body">
                <p><strong>{{ $item->ingresso->evento->nome ?? 'N/A' }}</strong></p>
                <p class="text-muted small">
                    <i class="bi bi-calendar"></i> 
                    {{ \Carbon\Carbon::parse($item->ingresso->evento->data_inicio ?? now())->format('d/m/Y') }}
                    <br>
                    <i class="bi bi-clock"></i> 
                    {{ $item->ingresso->evento->hora_inicio ?? 'N/A' }} - {{ $item->ingresso->evento->hora_fim ?? 'N/A' }}
                </p>
                <p class="text-muted small">
                    <i class="bi bi-geo-alt"></i> 
                    {{ $item->ingresso->evento->local->nome ?? 'N/A' }}
                </p>
                <p class="text-muted small">
                    <i class="bi bi-people"></i> 
                    Capacidade: {{ number_format($item->ingresso->evento->capacidade_total ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Ações Rápidas --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('carteira.qrcode', $item->id) }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-qr-code"></i> Gerar QR Code
                </a>
                @if($item->status == 'ativo')
                <a href="{{ route('transferencias.create', $item->id) }}" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-arrow-right"></i> Transferir Ingresso
                </a>
                <a href="{{ route('reembolsos.create', $item->id) }}" class="btn btn-danger w-100">
                    <i class="bi bi-arrow-return-left"></i> Solicitar Reembolso
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection