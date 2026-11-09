{{--
    ============================================================
    VIEW: Detalhes do Ingresso
    ============================================================
    Exibe todos os detalhes de um ingresso específico.
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Ingresso - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-ticket text-primary"></i> Detalhes do Ingresso</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('ingressos.index', $ingresso->evento_id) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        @if($ingresso->status == 'pago')
        <a href="{{ route('ingressos.qrcode', ['eventoId' => $ingresso->evento_id, 'id' => $ingresso->id]) }}" 
           class="btn btn-sm btn-primary ms-2">
            <i class="bi bi-qr-code"></i> QR Code
        </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Ingresso</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Evento:</strong>
                        <p class="mt-1">{{ $ingresso->evento->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p class="mt-1">
                            @if($ingresso->status == 'pago')
                                <span class="badge badge-status badge-published">Pago</span>
                            @elseif($ingresso->status == 'utilizado')
                                <span class="badge badge-status badge-ended">Utilizado</span>
                            @elseif($ingresso->status == 'cancelado')
                                <span class="badge badge-status badge-canceled">Cancelado</span>
                            @elseif($ingresso->status == 'disponivel')
                                <span class="badge badge-status badge-draft">Disponível</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Categoria:</strong>
                        <p class="mt-1">{{ $ingresso->categoria->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Preço Pago:</strong>
                        <p class="mt-1"><strong>{{ number_format($ingresso->preco_pago, 2, ',', '.') }} MZN</strong></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>QR Code:</strong>
                        <p class="mt-1"><code>{{ $ingresso->qr_code }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Data Compra:</strong>
                        <p class="mt-1">{{ \Carbon\Carbon::parse($ingresso->data_compra)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                @if($ingresso->data_utilizacao)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Data Utilização:</strong>
                        <p class="mt-1 text-secondary">{{ \Carbon\Carbon::parse($ingresso->data_utilizacao)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Participante --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Participante</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nome:</strong>
                        <p class="mt-1">{{ $ingresso->participante->nome_completo ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p class="mt-1">{{ $ingresso->participante->email ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Telefone:</strong>
                        <p class="mt-1">{{ $ingresso->participante->telefone ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>NUIT:</strong>
                        <p class="mt-1">{{ $ingresso->participante->nuit ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Checkin --}}
        @if($ingresso->checkin)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-check2-circle"></i> Check-in</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Data:</strong>
                        <p class="mt-1">{{ \Carbon\Carbon::parse($ingresso->checkin->data_checkin)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Porta:</strong>
                        <p class="mt-1">{{ $ingresso->checkin->porta_acesso ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p class="mt-1">
                            @if($ingresso->checkin->status == 'sincronizado')
                                <span class="badge badge-status badge-published">Sincronizado</span>
                            @else
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        {{-- QR Code --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-qr-code"></i> QR Code</h5>
            </div>
            <div class="card-body text-center">
                <div class="p-3 bg-light rounded">
                    {!! $qrCode !!}
                </div>
                <p class="mt-2 text-muted small">Escaneie para validar o ingresso</p>
            </div>
        </div>

        {{-- Ações --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                @if($ingresso->status == 'pago')
                <a href="{{ route('ingressos.qrcode', ['eventoId' => $ingresso->evento_id, 'id' => $ingresso->id]) }}" 
                   class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-qr-code"></i> Ver QR Code
                </a>
                <form action="{{ route('ingressos.cancelar', ['eventoId' => $ingresso->evento_id, 'id' => $ingresso->id]) }}" 
                      method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100" 
                            onclick="return confirm('Cancelar este ingresso?')">
                        <i class="bi bi-x-lg"></i> Cancelar Ingresso
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection