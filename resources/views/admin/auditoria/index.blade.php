{{--
    ============================================================
    VIEW: Lista de Auditoria
    ============================================================
    Exibe todos os logs do sistema com filtros.
    
    🔥 Multi-Organizador:
    - Organizadores veem apenas logs da sua organização
    - Admin veem todos os logs
--}}

@extends('layouts.admin')

@section('title', 'Auditoria - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clipboard-data text-primary"></i> Auditoria</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group">
            <button onclick="exportarLogs()" class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> Exportar
            </button>
            <button onclick="limparLogs()" class="btn btn-sm btn-danger">
                <i class="bi bi-trash"></i> Limpar
            </button>
        </div>
    </div>
</div>

{{-- Estatísticas --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-primary">{{ $estatisticas['total'] ?? 0 }}</div>
            <div class="label">Total de Logs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-success">{{ $estatisticas['criacoes'] ?? 0 }}</div>
            <div class="label text-success">Criações</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-warning">{{ $estatisticas['atualizacoes'] ?? 0 }}</div>
            <div class="label text-warning">Atualizações</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-danger">{{ $estatisticas['exclusoes'] ?? 0 }}</div>
            <div class="label text-danger">Exclusões</div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('auditoria.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Usuário</label>
                    <select name="user_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('user_id') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ação</label>
                    <select name="acao" class="form-select">
                        <option value="">Todas</option>
                        @foreach($acoes as $acao)
                            <option value="{{ $acao }}" {{ request('acao') == $acao ? 'selected' : '' }}>
                                {{ ucfirst($acao) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tabela</label>
                    <select name="tabela" class="form-select">
                        <option value="">Todas</option>
                        @foreach($tabelas as $tabela)
                            <option value="{{ $tabela }}" {{ request('tabela') == $tabela ? 'selected' : '' }}>
                                {{ ucfirst($tabela) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('auditoria.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Logs --}}
<div class="card">
    <div class="card-body">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Tabela</th>
                            <th>Registro</th>
                            <th>Data/Hora</th>
                            <th>IP</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>#{{ $log->id }}</td>
                            <td>
                                {{ $log->user->name ?? 'Sistema' }}
                                @if($log->user)
                                    <br>
                                    <small class="text-muted">{{ $log->user->email }}</small>
                                @endif
                            </td>
                            <td>
                                <i class="{{ $log->acao_icon }}"></i>
                                <span class="badge bg-{{ $log->acao == 'create' ? 'success' : ($log->acao == 'update' ? 'warning' : ($log->acao == 'delete' ? 'danger' : 'info')) }}">
                                    {{ $log->acao_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($log->tabela) }}</span>
                            </td>
                            <td>
                                <code>#{{ $log->registro_id }}</code>
                            </td>
                            <td>
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                                <br>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <code>{{ $log->ip ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <a href="{{ route('auditoria.show', $log->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-clipboard-data fs-1 text-muted"></i>
                <p class="text-muted mt-2">Nenhum log encontrado.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function exportarLogs() {
        const form = document.getElementById('filterForm');
        const action = '{{ route('auditoria.exportar') }}';
        form.action = action;
        form.method = 'GET';
        form.submit();
        setTimeout(() => {
            form.action = '';
            form.method = 'GET';
        }, 100);
    }

    function limparLogs() {
        Swal.fire({
            title: 'Limpar logs antigos?',
            text: 'Digite o número de dias para manter (padrão: 90):',
            icon: 'warning',
            input: 'number',
            inputValue: 90,
            inputAttributes: {
                min: 1,
                max: 365
            },
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Sim, limpar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const dias = result.value || 90;
                Swal.fire({
                    title: 'Processando...',
                    text: 'Aguarde enquanto os logs são removidos.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('{{ route('auditoria.limpar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ dias: dias })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Limpo!',
                            text: data.message,
                            timer: 3000
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Erro ao limpar logs');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: error.message
                    });
                });
            }
        });
    }
</script>
@endpush