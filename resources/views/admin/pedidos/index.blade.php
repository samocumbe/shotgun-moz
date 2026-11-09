{{--
    ============================================================
    VIEW: Lista de Pedidos
    ============================================================
    Exibe todos os pedidos com filtros.
    SEM DATATABLES - apenas paginação do Laravel.
--}}

@extends('layouts.admin')

@section('title', 'Pedidos - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Pedidos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('vendas.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Nova Venda
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('pedidos.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="evento_id" class="form-label">Evento</label>
                <select class="form-select" id="evento_id" name="evento_id">
                    <option value="">Todos os eventos</option>
                    @foreach($eventos ?? [] as $evento)
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
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                    <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    <option value="reembolsado" {{ request('status') == 'reembolsado' ? 'selected' : '' }}>Reembolsado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="tipo_venda" class="form-label">Tipo</label>
                <select class="form-select" id="tipo_venda" name="tipo_venda">
                    <option value="">Todos</option>
                    <option value="presencial" {{ request('tipo_venda') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                    <option value="online" {{ request('tipo_venda') == 'online' ? 'selected' : '' }}>Online</option>
                    <option value="promoter" {{ request('tipo_venda') == 'promoter' ? 'selected' : '' }}>Promoter</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Cliente, pedido..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Pedidos --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th># Pedido</th>
                        <th>Cliente</th>
                        <th>Evento</th>
                        <th>Total</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                    <tr>
                        <td><code>{{ $pedido->numero_pedido }}</code></td>
                        <td>
                            <strong>{{ $pedido->participante->nome_completo ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $pedido->participante->email ?? '' }}</small>
                        </td>
                        <td>{{ $pedido->evento->nome ?? 'N/A' }}</td>
                        <td><strong>{{ number_format($pedido->total, 2, ',', '.') }} MZN</strong></td>
                        <td>
                            @if($pedido->tipo_venda == 'presencial')
                                <span class="badge bg-primary">Presencial</span>
                            @elseif($pedido->tipo_venda == 'online')
                                <span class="badge bg-info">Online</span>
                            @elseif($pedido->tipo_venda == 'promoter')
                                <span class="badge bg-warning text-dark">Promoter</span>
                            @endif
                        </td>
                        <td>
                            @if($pedido->status == 'pago')
                                <span class="badge badge-status badge-published">Pago</span>
                            @elseif($pedido->status == 'pendente')
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @elseif($pedido->status == 'cancelado')
                                <span class="badge badge-status badge-canceled">Cancelado</span>
                            @elseif($pedido->status == 'reembolsado')
                                <span class="badge badge-status badge-ended">Reembolsado</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('pedidos.show', $pedido->id) }}" 
                               class="btn btn-sm btn-info" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhum pedido encontrado.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- PAGINAÇÃO DO LARAVEL (APENAS UMA) --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Mostrando {{ $pedidos->firstItem() ?? 0 }} até {{ $pedidos->lastItem() ?? 0 }} 
                    de {{ $pedidos->total() }} registros
                </small>
            </div>
            <div>
                {{ $pedidos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection