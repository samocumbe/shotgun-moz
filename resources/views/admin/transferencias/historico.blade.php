{{--
    ============================================================
    VIEW: Histórico de Transferências
    ============================================================
    Lista todas as transferências do usuário.
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Histórico de Transferências - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clock-history text-primary"></i> Histórico de Transferências</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('carteira.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

{{-- Estatísticas --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-primary">{{ $transferencias->total() }}</div>
            <div class="label">Total de Transferências</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-warning">{{ $transferencias->where('status', 'pendente')->count() }}</div>
            <div class="label text-warning">Pendentes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-success">{{ $transferencias->where('status', 'confirmado')->count() }}</div>
            <div class="label text-success">Confirmadas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-danger">{{ $transferencias->where('status', 'cancelada')->count() }}</div>
            <div class="label text-danger">Canceladas</div>
        </div>
    </div>
</div>

{{-- Lista de Transferências --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list"></i> Transferências</h5>
    </div>
    <div class="card-body">
        @if($transferencias->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ingresso</th>
                            <th>De</th>
                            <th>Para</th>
                            <th>Código</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transferencias as $transferencia)
                        <tr>
                            <td>#{{ $transferencia->id }}</td>
                            <td>
                                {{ $transferencia->carteiraIngresso->ingresso->evento->nome ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $transferencia->usuarioOrigem->name ?? 'N/A' }}
                                @if($transferencia->usuarioOrigem->id == auth()->id())
                                    <span class="badge bg-info ms-1">Você</span>
                                @endif
                            </td>
                            <td>
                                {{ $transferencia->usuarioDestino->name ?? 'N/A' }}
                                @if($transferencia->usuarioDestino->id == auth()->id())
                                    <span class="badge bg-info ms-1">Você</span>
                                @endif
                            </td>
                            <td>
                                <code>{{ $transferencia->codigo_confirmacao }}</code>
                            </td>
                            <td>
                                @if($transferencia->status == 'pendente')
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                @elseif($transferencia->status == 'confirmado')
                                    <span class="badge bg-success">Confirmado</span>
                                @elseif($transferencia->status == 'cancelada')
                                    <span class="badge bg-danger">Cancelada</span>
                                @endif
                            </td>
                            <td>
                                {{ $transferencia->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @if($transferencia->status == 'pendente' && $transferencia->usuario_destino_id == auth()->id())
                                    <a href="{{ route('transferencias.confirmar', $transferencia->id) }}" 
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i> Confirmar
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $transferencias->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2">Nenhuma transferência encontrada.</p>
            </div>
        @endif
    </div>
</div>
@endsection