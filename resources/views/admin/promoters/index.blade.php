{{--
    ============================================================
    VIEW: Lista de Promoters
    ============================================================
    Exibe todos os promoters cadastrados com opções de:
    - Criar novo promoter
    - Visualizar detalhes
    - Editar
    - Ver comissões
    - Excluir
--}}

@extends('layouts.admin')

@section('title', 'Promoters - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Promoters</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('promoters.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Novo Promoter
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('promoters.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Nome, email, código..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="{{ route('promoters.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Promoters --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Código</th>
                        <th>Comissão</th>
                        <th>Vendas</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promoters as $promoter)
                    <tr>
                        <td>
                            <strong>{{ $promoter->nome }}</strong><br>
                            <small class="text-muted">{{ $promoter->telefone }}</small>
                        </td>
                        <td>{{ $promoter->email }}</td>
                        <td>
                            <code>{{ $promoter->codigo_promocional }}</code>
                            <br>
                            <small class="text-muted">{{ $promoter->link_personalizado }}</small>
                        </td>
                        <td>{{ number_format($promoter->comissao_padrao, 0) }}%</td>
                        <td>
                            <strong>{{ number_format($promoter->total_vendas, 2, ',', '.') }} MZN</strong>
                            <br>
                            <small class="text-muted">Comissão: {{ number_format($promoter->total_comissao, 2, ',', '.') }} MZN</small>
                        </td>
                        <td>
                            @if($promoter->status == 'ativo')
                                <span class="badge badge-status badge-published">Ativo</span>
                            @elseif($promoter->status == 'inativo')
                                <span class="badge badge-status badge-canceled">Inativo</span>
                            @elseif($promoter->status == 'pendente')
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('promoters.show', $promoter->id) }}" 
                               class="btn btn-sm btn-info" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('promoters.edit', $promoter->id) }}" 
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('promoters.comissoes', $promoter->id) }}" 
                               class="btn btn-sm btn-success" title="Comissões">
                                <i class="bi bi-coin"></i>
                            </a>
                            @if($promoter->status != 'ativo')
                            <button onclick="confirmDelete({{ $promoter->id }}, '{{ $promoter->nome }}')" 
                                    class="btn btn-sm btn-danger" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="delete-form-{{ $promoter->id }}" 
                                  action="{{ route('promoters.destroy', $promoter->id) }}" 
                                  method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhum promoter cadastrado.</p>
                            <a href="{{ route('promoters.create') }}" class="btn btn-sm btn-primary">
                                Cadastrar primeiro promoter
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Paginação --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Mostrando {{ $promoters->firstItem() ?? 0 }} até {{ $promoters->lastItem() ?? 0 }} 
                    de {{ $promoters->total() }} registros
                </small>
            </div>
            <div>
                {{ $promoters->links() }}
            </div>
        </div>
    </div>
</div>
@endsection