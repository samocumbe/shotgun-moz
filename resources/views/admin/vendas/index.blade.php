{{--
    ============================================================
    VIEW: Lista de Vendas
    ============================================================
    Exibe todas as vendas realizadas com filtros.
    SEM DATATABLES - apenas paginação do Laravel.
--}}

@extends('layouts.admin')

@section('title', 'Vendas - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Vendas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('vendas.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Nova Venda
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('vendas.index') }}" method="GET" class="row g-3">
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
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="presencial" {{ request('tipo') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                    <option value="online" {{ request('tipo') == 'online' ? 'selected' : '' }}>Online</option>
                    <option value="promoter" {{ request('tipo') == 'promoter' ? 'selected' : '' }}>Promoter</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="confirmada" {{ request('status') == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Nome, email, telefone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Vendas --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Evento</th>
                        <th>Valor</th>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendas as $venda)
                    <tr>
                        <td>{{ $venda->id }}</td>
                        <td>
                            <strong>{{ $venda->participante->nome_completo ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $venda->participante->email ?? '' }}</small>
                        </td>
                        <td>{{ $venda->evento->nome ?? 'N/A' }}</td>
                        <td><strong>{{ number_format($venda->valor_total, 2, ',', '.') }} MZN</strong></td>
                        <td>
                            @if($venda->tipo == 'presencial')
                                <span class="badge bg-primary">Presencial</span>
                            @elseif($venda->tipo == 'online')
                                <span class="badge bg-info">Online</span>
                            @elseif($venda->tipo == 'promoter')
                                <span class="badge bg-warning text-dark">Promoter</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($venda->status == 'confirmada')
                                <span class="badge badge-status badge-published">Confirmada</span>
                            @elseif($venda->status == 'pendente')
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @elseif($venda->status == 'cancelada')
                                <span class="badge badge-status badge-canceled">Cancelada</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('vendas.show', $venda->id) }}" 
                               class="btn btn-sm btn-info" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-cart" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhuma venda registrada.</p>
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
                    Mostrando {{ $vendas->firstItem() ?? 0 }} até {{ $vendas->lastItem() ?? 0 }} 
                    de {{ $vendas->total() }} registros
                </small>
            </div>
            <div>
                {{ $vendas->links() }}
            </div>
        </div>
    </div>
</div>
@endsection