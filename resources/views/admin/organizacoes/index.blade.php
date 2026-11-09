{{--
    ============================================================
    VIEW: Lista de Organizações
    ============================================================
    Exibe todas as organizações cadastradas com opções de:
    - Criar nova organização
    - Visualizar detalhes
    - Editar
    - Excluir (apenas se não tiver eventos)
    - Estatísticas de cada organização (usuários, eventos)
--}}

@extends('layouts.admin')

@section('title', 'Organizações - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-building text-primary"></i> Organizações</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('organizacoes.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Nova Organização
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('organizacoes.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                        <option value="suspenso" {{ request('status') == 'suspenso' ? 'selected' : '' }}>Suspenso</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nome, email ou telefone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('organizacoes.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Estatísticas --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-primary">{{ $organizacoes->total() }}</div>
            <div class="label">Total de Organizações</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-success">{{ $organizacoes->where('status', 'ativo')->count() }}</div>
            <div class="label text-success">Ativas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-danger">{{ $organizacoes->where('status', 'inativo')->count() }}</div>
            <div class="label text-danger">Inativas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-warning">{{ $organizacoes->where('status', 'suspenso')->count() }}</div>
            <div class="label text-warning">Suspensas</div>
        </div>
    </div>
</div>

{{-- Tabela de Organizações --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Usuários</th>
                        <th>Eventos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizacoes as $organizacao)
                    <tr>
                        <td>#{{ $organizacao->id }}</td>
                        <td>
                            <strong>{{ $organizacao->nome }}</strong>
                            @if($organizacao->logo)
                                <br>
                                <img src="{{ asset('storage/' . $organizacao->logo) }}" alt="Logo" style="max-height: 30px;">
                            @endif
                        </td>
                        <td>{{ $organizacao->email ?? 'N/A' }}</td>
                        <td>{{ $organizacao->telefone ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $organizacao->usuarios_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $organizacao->eventos_count ?? 0 }}</span>
                        </td>
                        <td>
                            @if($organizacao->status == 'ativo')
                                <span class="badge bg-success">Ativo</span>
                            @elseif($organizacao->status == 'inativo')
                                <span class="badge bg-danger">Inativo</span>
                            @elseif($organizacao->status == 'suspenso')
                                <span class="badge bg-warning text-dark">Suspenso</span>
                            @endif
                        </td>
                        <td>
                            {{-- Botão Visualizar --}}
                            <a href="{{ route('organizacoes.show', $organizacao->id) }}" 
                               class="btn btn-sm btn-info" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            {{-- Botão Editar --}}
                            <a href="{{ route('organizacoes.edit', $organizacao->id) }}" 
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            {{-- Botão Excluir --}}
                            @if($organizacao->eventos_count == 0)
                                <button onclick="confirmDelete({{ $organizacao->id }}, '{{ $organizacao->nome }}')" 
                                        class="btn btn-sm btn-danger" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <form id="delete-form-{{ $organizacao->id }}" 
                                      action="{{ route('organizacoes.destroy', $organizacao->id) }}" 
                                      method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @else
                                <button class="btn btn-sm btn-secondary" disabled title="Possui eventos associados">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-building" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhuma organização cadastrada.</p>
                            <a href="{{ route('organizacoes.create') }}" class="btn btn-sm btn-primary">
                                Criar primeira organização
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Paginação --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $organizacoes->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id, nome) {
        Swal.fire({
            title: 'Tem certeza?',
            html: `Deseja realmente excluir a organização <strong>"${nome}"</strong>?<br><br>Esta ação não pode ser desfeita.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="bi bi-trash"></i> Sim, excluir!',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush