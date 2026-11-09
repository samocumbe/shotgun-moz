{{--
    ============================================================
    VIEW: Admin - Lista de Reembolsos
    ============================================================
    Exibe todas as solicitações de reembolso para administradores.
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Reembolsos - Administração - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-arrow-return-left text-danger"></i> Reembolsos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="text-muted">
            <i class="bi bi-clock"></i> Total: <strong>{{ $reembolsos->total() }}</strong>
        </span>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reembolsos') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="rejeitado" {{ request('status') == 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                        <option value="processado" {{ request('status') == 'processado' ? 'selected' : '' }}>Processado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.reembolsos') }}" class="btn btn-secondary">
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
            <div class="number text-warning">{{ $reembolsos->where('status', 'pendente')->count() }}</div>
            <div class="label text-warning">Pendentes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-success">{{ $reembolsos->where('status', 'aprovado')->count() }}</div>
            <div class="label text-success">Aprovados</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-danger">{{ $reembolsos->where('status', 'rejeitado')->count() }}</div>
            <div class="label text-danger">Rejeitados</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-info">{{ $reembolsos->where('status', 'processado')->count() }}</div>
            <div class="label text-info">Processados</div>
        </div>
    </div>
</div>

{{-- Tabela --}}
<div class="card">
    <div class="card-body">
        @if($reembolsos->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Evento</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reembolsos as $reembolso)
                        <tr>
                            <td>#{{ $reembolso->id }}</td>
                            <td>
                                {{ $reembolso->user->name ?? 'N/A' }}
                                <br>
                                <small class="text-muted">{{ $reembolso->user->email ?? '' }}</small>
                            </td>
                            <td>
                                {{ $reembolso->carteiraIngresso->ingresso->evento->nome ?? 'N/A' }}
                            </td>
                            <td>
                                <strong>{{ number_format($reembolso->valor_reembolso, 2, ',', '.') }} MZN</strong>
                            </td>
                            <td>
                                @if($reembolso->status == 'pendente')
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                @elseif($reembolso->status == 'aprovado')
                                    <span class="badge bg-success">Aprovado</span>
                                @elseif($reembolso->status == 'rejeitado')
                                    <span class="badge bg-danger">Rejeitado</span>
                                @elseif($reembolso->status == 'processado')
                                    <span class="badge bg-info">Processado</span>
                                @endif
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($reembolso->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <a href="{{ route('reembolsos.show', $reembolso->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($reembolso->status == 'pendente')
                                    <button onclick="aprovarReembolso({{ $reembolso->id }})" 
                                            class="btn btn-sm btn-success" title="Aprovar">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button onclick="rejeitarReembolso({{ $reembolso->id }})" 
                                            class="btn btn-sm btn-danger" title="Rejeitar">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                @endif
                                @if($reembolso->status == 'aprovado')
                                    <button onclick="processarReembolso({{ $reembolso->id }})" 
                                            class="btn btn-sm btn-primary" title="Processar">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $reembolsos->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2">Nenhuma solicitação de reembolso encontrada.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function aprovarReembolso(id) {
        Swal.fire({
            title: 'Aprovar reembolso?',
            text: 'Deseja realmente aprovar esta solicitação?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, aprovar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processando...',
                    text: 'Aguarde.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('/admin/reembolsos-admin/' + id + '/aprovar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Aprovado!',
                            timer: 2000
                        }).then(() => window.location.reload());
                    } else {
                        throw new Error(data.message || 'Erro ao aprovar');
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

    function rejeitarReembolso(id) {
        Swal.fire({
            title: 'Rejeitar reembolso?',
            text: 'Deseja realmente rejeitar esta solicitação?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, rejeitar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processando...',
                    text: 'Aguarde.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('/admin/reembolsos-admin/' + id + '/rejeitar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Rejeitado!',
                            timer: 2000
                        }).then(() => window.location.reload());
                    } else {
                        throw new Error(data.message || 'Erro ao rejeitar');
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

    function processarReembolso(id) {
        Swal.fire({
            title: 'Processar reembolso?',
            text: 'Confirme para processar o reembolso financeiro.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, processar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processando...',
                    text: 'Aguarde.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('/admin/reembolsos-admin/' + id + '/processar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Processado!',
                            timer: 2000
                        }).then(() => window.location.reload());
                    } else {
                        throw new Error(data.message || 'Erro ao processar');
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