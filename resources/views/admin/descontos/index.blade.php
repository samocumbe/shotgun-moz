{{--
    ============================================================
    VIEW: Lista de Descontos
    ============================================================
    Exibe todos os descontos com filtros.
    SEM DATATABLES - apenas paginação do Laravel.
--}}

@extends('layouts.admin')

@section('title', 'Descontos - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Descontos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('descontos.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Novo Desconto
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('descontos.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="evento_id" class="form-label">Evento</label>
                <select class="form-select" id="evento_id" name="evento_id">
                    <option value="">Todos</option>
                    @foreach($eventos ?? [] as $evento)
                        <option value="{{ $evento->id }}" {{ request('evento_id') == $evento->id ? 'selected' : '' }}>
                            {{ $evento->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="ativo" class="form-label">Status</label>
                <select class="form-select" id="ativo" name="ativo">
                    <option value="">Todos</option>
                    <option value="1" {{ request('ativo') == '1' ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ request('ativo') == '0' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="percentual" {{ request('tipo') == 'percentual' ? 'selected' : '' }}>Percentual</option>
                    <option value="fixo" {{ request('tipo') == 'fixo' ? 'selected' : '' }}>Fixo (MZN)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Código, nome..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Descontos --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Evento</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Usos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cupons as $desconto)
                    <tr>
                        <td><code>{{ $desconto->codigo }}</code></td>
                        <td>{{ $desconto->nome }}</td>
                        <td>{{ $desconto->evento->nome ?? 'Todos os eventos' }}</td>
                        <td>
                            @if($desconto->tipo == 'percentual')
                                <span class="badge bg-info">% Percentual</span>
                            @else
                                <span class="badge bg-warning text-dark">Fixo</span>
                            @endif
                        </td>
                        <td>
                            @if($desconto->tipo == 'percentual')
                                {{ number_format($desconto->valor, 0) }}%
                            @else
                                {{ number_format($desconto->valor, 2, ',', '.') }} MZN
                            @endif
                        </td>
                        <td>{{ $desconto->usos }}/{{ $desconto->quantidade ?? '∞' }}</td>
                        <td>
                            @if($desconto->ativo)
                                <span class="badge badge-status badge-published">Ativo</span>
                            @else
                                <span class="badge badge-status badge-draft">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('descontos.edit', $desconto->id) }}" 
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button onclick="confirmDelete({{ $desconto->id }}, '{{ $desconto->codigo }}')" 
                                    class="btn btn-sm btn-danger" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="delete-form-{{ $desconto->id }}" 
                                  action="{{ route('descontos.destroy', $desconto->id) }}" 
                                  method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-ticket" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhum desconto cadastrado.</p>
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
                    Mostrando {{ $cupons->firstItem() ?? 0 }} até {{ $cupons->lastItem() ?? 0 }} 
                    de {{ $cupons->total() }} registros
                </small>
            </div>
            <div>
                {{ $cupons->links() }}
            </div>
        </div>
    </div>
</div>
@endsection