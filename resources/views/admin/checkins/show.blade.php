{{--
    ============================================================
    VIEW: Detalhes do Check-in
    ============================================================
    Exibe todas as informações de um check-in específico.
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Check-in - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-check2-circle text-success"></i> Detalhes do Check-in #{{ $checkin->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('checkins.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {{-- Informações do Check-in --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Check-in</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID:</strong> #{{ $checkin->id }}
                    </div>
                    <div class="col-md-6">
                        <strong>Data/Hora:</strong> {{ \Carbon\Carbon::parse($checkin->data_checkin)->format('d/m/Y H:i:s') }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Evento:</strong> {{ $checkin->evento->nome ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        @if($checkin->status == 'sincronizado')
                            <span class="badge bg-success">Sincronizado</span>
                        @else
                            <span class="badge bg-warning text-dark">Pendente</span>
                        @endif
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Dispositivo:</strong> {{ $checkin->dispositivo->nome ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Modelo:</strong> {{ $checkin->dispositivo->modelo ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Porta de Acesso:</strong> {{ $checkin->porta_acesso ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Localização:</strong> {{ $checkin->localizacao ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>QR Code:</strong> <code>{{ $checkin->qr_code }}</code>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Ingresso --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-ticket"></i> Ingresso</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID do Ingresso:</strong> #{{ $checkin->ingresso->id ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Categoria:</strong> {{ $checkin->ingresso->categoria->nome ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Preço Pago:</strong> 
                        {{ isset($checkin->ingresso) ? number_format($checkin->ingresso->preco_pago, 2, ',', '.') . ' MZN' : 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Status do Ingresso:</strong>
                        @if($checkin->ingresso)
                            @if($checkin->ingresso->status == 'pago')
                                <span class="badge bg-success">Pago</span>
                            @elseif($checkin->ingresso->status == 'utilizado')
                                <span class="badge bg-info">Utilizado</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ $checkin->ingresso->status }}</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Sidebar: Participante --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Participante</h5>
            </div>
            <div class="card-body">
                @if($checkin->ingresso && $checkin->ingresso->participante)
                    <div class="mb-2">
                        <strong>Nome:</strong> {{ $checkin->ingresso->participante->nome_completo }}
                    </div>
                    <div class="mb-2">
                        <strong>Email:</strong> {{ $checkin->ingresso->participante->email }}
                    </div>
                    <div class="mb-2">
                        <strong>Telefone:</strong> {{ $checkin->ingresso->participante->telefone ?? 'N/A' }}
                    </div>
                    @if($checkin->ingresso->participante->cpf)
                    <div class="mb-2">
                        <strong>Documento:</strong> {{ $checkin->ingresso->participante->cpf }}
                    </div>
                    @endif
                @else
                    <p class="text-muted">Participante não encontrado</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection