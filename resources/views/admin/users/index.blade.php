{{--
    ============================================================
    VIEW: Lista de Usuários
    ============================================================
    Exibe todos os usuários do sistema com filtros e estatísticas.
    
    🔥 Multi-Organizador:
    - Filtro por organização (apenas admin)
    - Exibe organização de cada usuário
--}}

@extends('layouts.admin')

@section('title', 'Usuários - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people text-primary"></i> Usuários</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Usuário
        </a>
    </div>
</div>

{{-- Estatísticas --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number">{{ $total ?? 0 }}</div>
            <div class="label">Total de Usuários</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-danger">{{ $admins ?? 0 }}</div>
            <div class="label text-danger">Administradores</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-info">{{ $operadores ?? 0 }}</div>
            <div class="label text-info">Operadores</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-warning">{{ $promoters ?? 0 }}</div>
            <div class="label text-warning">Promoters</div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nome ou email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Papel</label>
                    <select name="role" class="form-select">
                        <option value="">Todos</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="operador" {{ request('role') == 'operador' ? 'selected' : '' }}>Operador</option>
                        <option value="promoter" {{ request('role') == 'promoter' ? 'selected' : '' }}>Promoter</option>
                        <option value="cliente" {{ request('role') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                    </select>
                </div>
                
                {{-- 🔥 MULTI-ORGANIZADOR: Filtro por organização (apenas admin) --}}
                @if(auth()->user()->isAdmin() && isset($organizacoes) && $organizacoes->count() > 0)
                <div class="col-md-3">
                    <label class="form-label">Organização</label>
                    <select name="organizacao_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($organizacoes as $org)
                            <option value="{{ $org->id }}" {{ request('organizacao_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Usuários --}}
<div class="card">
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Papel</th>
                            <th>Organização</th>
                            <th>Ingressos</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>#{{ $user->id }}</td>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                @if($user->id === auth()->id())
                                    <span class="badge bg-success ms-1">Você</span>
                                @endif
                                @if($user->is_organizador)
                                    <span class="badge bg-info ms-1">Organizador</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->telefone ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role_color }}">
                                    {{ $user->role_name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $user->organizacao->nome ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $user->carteira->count() ?? 0 }}</span>
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <button onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')" 
                                            class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $user->id }}" 
                                          action="{{ route('users.destroy', $user->id) }}" 
                                          method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-people fs-1 text-muted"></i>
                <p class="text-muted mt-2">Nenhum usuário encontrado.</p>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Criar primeiro usuário
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Tem certeza?',
            html: `Deseja realmente excluir o usuário <strong>"${name}"</strong>?<br><br>Esta ação não pode ser desfeita.`,
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