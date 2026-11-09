@extends('layouts.admin')

@section('title', 'Transferir Ingresso - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-arrow-right text-warning"></i> Transferir Ingresso</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('carteira.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Dados do Ingresso</h5>
            </div>
            <div class="card-body">
                <p><strong>Evento:</strong> {{ $carteira->ingresso->evento->nome ?? 'N/A' }}</p>
                <p><strong>Código:</strong> <code>{{ $carteira->codigo_acesso }}</code></p>
                <p><strong>Status:</strong> 
                    <span class="badge badge-status badge-published">Ativo</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transferir para</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('transferencias.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="carteira_id" value="{{ $carteira->id }}">
                    
                    <div class="mb-3">
                        <label for="email_destino" class="form-label">Email do destinatário</label>
                        <input type="email" class="form-control @error('email_destino') is-invalid @enderror" 
                               id="email_destino" name="email_destino" required>
                        <small class="text-muted">O destinatário deve ter uma conta no sistema.</small>
                        @error('email_destino')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-send"></i> Enviar Transferência
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection