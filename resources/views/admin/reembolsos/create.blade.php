@extends('layouts.admin')

@section('title', 'Solicitar Reembolso - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-arrow-return-left text-danger"></i> Solicitar Reembolso</h1>
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
                <p><strong>Valor Pago:</strong> {{ number_format($carteira->ingresso->preco_pago ?? 0, 2, ',', '.') }} MZN</p>
                <p><strong>Status:</strong> 
                    <span class="badge badge-status badge-published">Ativo</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Solicitar Reembolso</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reembolsos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="carteira_id" value="{{ $carteira->id }}">
                    
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo do Reembolso</label>
                        <select class="form-select @error('motivo') is-invalid @enderror" 
                                id="motivo" name="motivo" required>
                            <option value="">Selecione...</option>
                            <option value="Mudança de planos">Mudança de planos</option>
                            <option value="Evento cancelado">Evento cancelado</option>
                            <option value="Data incompatível">Data incompatível</option>
                            <option value="Compra por engano">Compra por engano</option>
                            <option value="Outro">Outro</option>
                        </select>
                        @error('motivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                                  id="observacoes" name="observacoes" rows="3">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Ao solicitar reembolso, o ingresso será cancelado e não poderá mais ser utilizado.
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-send"></i> Solicitar Reembolso
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection