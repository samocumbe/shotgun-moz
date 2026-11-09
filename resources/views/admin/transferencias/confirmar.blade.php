{{--
    ============================================================
    VIEW: Confirmar Transferência
    ============================================================
    Exibe os detalhes da transferência e solicita o código de confirmação.
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Confirmar Transferência - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-check-circle text-success"></i> Confirmar Transferência</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('carteira.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Detalhes da Transferência</h5>
            </div>
            <div class="card-body">
                {{-- Dados da Transferência --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>De:</strong> {{ $transferencia->usuarioOrigem->name ?? 'N/A' }}
                        <br>
                        <small class="text-muted">{{ $transferencia->usuarioOrigem->email ?? 'N/A' }}</small>
                    </div>
                    <div class="col-md-6">
                        <strong>Para:</strong> {{ $transferencia->usuarioDestino->name ?? 'N/A' }}
                        <br>
                        <small class="text-muted">{{ $transferencia->usuarioDestino->email ?? 'N/A' }}</small>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Evento:</strong> 
                        {{ $transferencia->carteiraIngresso->ingresso->evento->nome ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Código do Ingresso:</strong> 
                        <code>{{ $transferencia->carteiraIngresso->codigo_acesso ?? 'N/A' }}</code>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        @if($transferencia->status == 'pendente')
                            <span class="badge bg-warning text-dark">Aguardando confirmação</span>
                        @elseif($transferencia->status == 'confirmado')
                            <span class="badge bg-success">Confirmado</span>
                        @else
                            <span class="badge bg-danger">{{ $transferencia->status }}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Data da Solicitação:</strong> 
                        {{ $transferencia->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>

                <hr>

                {{-- Formulário de Confirmação --}}
                <form action="{{ route('transferencias.confirmar-store', $transferencia->id) }}" method="POST">
                    @csrf

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Código de Confirmação:</strong> 
                        <code class="fs-5">{{ $transferencia->codigo_confirmacao }}</code>
                        <br>
                        <small>Informe o código acima para confirmar a transferência.</small>
                    </div>

                    <div class="mb-3">
                        <label for="codigo_confirmacao" class="form-label">Código de Confirmação <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('codigo_confirmacao') is-invalid @enderror" 
                               id="codigo_confirmacao" name="codigo_confirmacao" 
                               placeholder="Digite o código de confirmação" required>
                        @error('codigo_confirmacao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('carteira.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Confirmar Transferência
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection