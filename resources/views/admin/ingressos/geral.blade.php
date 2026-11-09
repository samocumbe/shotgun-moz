@extends('layouts.admin')

@section('title', 'Ingressos - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-ticket text-primary"></i> Ingressos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="text-muted">
            <i class="bi bi-database"></i> Total: <strong>{{ $ingressos->total() }}</strong> ingressos
        </span>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('ingressos.geral') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="evento_id" class="form-label">Evento</label>
                <select class="form-select" id="evento_id" name="evento_id">
                    <option value="">Todos os eventos</option>
                    @foreach($eventos as $evento)
                        <option value="{{ $evento->id }}" {{ request('evento_id') == $evento->id ? 'selected' : '' }}>
                            {{ $evento->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                    <option value="utilizado" {{ request('status') == 'utilizado' ? 'selected' : '' }}>Utilizado</option>
                    <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    <option value="disponivel" {{ request('status') == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="QR Code, Cliente..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary w-50">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('ingressos.geral') }}" class="btn btn-secondary w-50">
                    <i class="bi bi-arrow-clockwise"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Ingressos --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>QR Code</th>
                        <th>Evento</th>
                        <th>Cliente</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ingressos as $ingresso)
                    <tr>
                        <td>{{ $ingresso->id }}</td>
                        <td><code>{{ $ingresso->qr_code }}</code></td>
                        <td>{{ $ingresso->evento->nome ?? 'N/A' }}</td>
                        <td>{{ $ingresso->participante->nome_completo ?? 'N/A' }}</td>
                        <td><strong>{{ number_format($ingresso->preco_pago, 2, ',', '.') }} MZN</strong></td>
                        <td>
                            @if($ingresso->status == 'pago')
                                <span class="badge badge-status badge-published">Pago</span>
                            @elseif($ingresso->status == 'utilizado')
                                <span class="badge badge-status badge-ended">Utilizado</span>
                            @elseif($ingresso->status == 'cancelado')
                                <span class="badge badge-status badge-canceled">Cancelado</span>
                            @elseif($ingresso->status == 'disponivel')
                                <span class="badge badge-status badge-draft">Disponível</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($ingresso->created_at)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('ingressos.show', ['eventoId' => $ingresso->evento_id, 'id' => $ingresso->id]) }}" 
                               class="btn btn-sm btn-info" title="Ver detalhes">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($ingresso->status == 'pago')
                            <a href="{{ route('ingressos.qrcode', ['eventoId' => $ingresso->evento_id, 'id' => $ingresso->id]) }}" 
                               class="btn btn-sm btn-primary" title="QR Code">
                                <i class="bi bi-qr-code"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-ticket" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhum ingresso encontrado.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $ingressos->links() }}
    </div>
</div>
@endsection