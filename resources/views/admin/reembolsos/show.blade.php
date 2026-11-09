{{--
    ============================================================
    VIEW: Detalhes do Reembolso
    ============================================================
    Exibe todas as informações de uma solicitação de reembolso.
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Reembolso - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-arrow-return-left text-danger"></i> 
        Detalhes do Reembolso #{{ $reembolso->id }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.reembolsos') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    {{-- COLUNA PRINCIPAL --}}
    <div class="col-md-8">
        {{-- Informações do Reembolso --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Reembolso</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID:</strong> #{{ $reembolso->id }}
                    </div>
                    <div class="col-md-6">
                        <strong>Data da Solicitação:</strong> 
                        {{ \Carbon\Carbon::parse($reembolso->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        @if($reembolso->status == 'pendente')
                            <span class="badge bg-warning text-dark">Pendente</span>
                        @elseif($reembolso->status == 'aprovado')
                            <span class="badge bg-success">Aprovado</span>
                        @elseif($reembolso->status == 'rejeitado')
                            <span class="badge bg-danger">Rejeitado</span>
                        @elseif($reembolso->status == 'processado')
                            <span class="badge bg-info">Processado</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Valor:</strong> 
                        <h4 class="text-danger">{{ number_format($reembolso->valor_reembolso, 2, ',', '.') }} MZN</h4>
                    </div>
                </div>
                @if($reembolso->motivo)
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Motivo:</strong>
                        <p class="mt-1">{{ $reembolso->motivo }}</p>
                    </div>
                </div>
                @endif
                @if($reembolso->observacoes)
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Observações:</strong>
                        <p class="mt-1">{{ $reembolso->observacoes }}</p>
                    </div>
                </div>
                @endif
                @if($reembolso->data_aprovacao)
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Data de Aprovação:</strong>
                        {{ \Carbon\Carbon::parse($reembolso->data_aprovacao)->format('d/m/Y H:i') }}
                    </div>
                </div>
                @endif
                @if($reembolso->data_processamento)
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Data de Processamento:</strong>
                        {{ \Carbon\Carbon::parse($reembolso->data_processamento)->format('d/m/Y H:i') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Ingresso --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-ticket"></i> Ingresso</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Código:</strong> 
                        <code>{{ $reembolso->carteiraIngresso->codigo_acesso ?? 'N/A' }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Evento:</strong> 
                        {{ $reembolso->carteiraIngresso->ingresso->evento->nome ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Categoria:</strong> 
                        {{ $reembolso->carteiraIngresso->ingresso->categoria->nome ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Preço Pago:</strong> 
                        {{ number_format($reembolso->carteiraIngresso->ingresso->preco_pago ?? 0, 2, ',', '.') }} MZN
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SIDEBAR --}}
    <div class="col-md-4">
        {{-- Cliente --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Cliente</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Nome:</strong> {{ $reembolso->user->name ?? 'N/A' }}
                </div>
                <div class="mb-2">
                    <strong>Email:</strong> {{ $reembolso->user->email ?? 'N/A' }}
                </div>
                <div class="mb-2">
                    <strong>Telefone:</strong> {{ $reembolso->user->telefone ?? 'N/A' }}
                </div>
            </div>
        </div>

        {{-- Ações Administrativas --}}
        @if($reembolso->status == 'pendente')
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                <button onclick="aprovarReembolso({{ $reembolso->id }})" 
                        class="btn btn-success w-100 mb-2">
                    <i class="bi bi-check-circle"></i> Aprovar
                </button>
                <button onclick="rejeitarReembolso({{ $reembolso->id }})" 
                        class="btn btn-danger w-100">
                    <i class="bi bi-x-circle"></i> Rejeitar
                </button>
            </div>
        </div>
        @elseif($reembolso->status == 'aprovado')
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                <button onclick="processarReembolso({{ $reembolso->id }})" 
                        class="btn btn-primary w-100">
                    <i class="bi bi-arrow-repeat"></i> Processar
                </button>
            </div>
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
            text: 'Deseja realmente aprovar esta solicitação de reembolso?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, aprovar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/admin/reembolsos/admin/' + id + '/aprovar', {
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
                            text: data.message,
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
                        });
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
            text: 'Deseja realmente rejeitar esta solicitação de reembolso?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, rejeitar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/admin/reembolsos/admin/' + id + '/rejeitar', {
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
                            text: data.message,
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
                        });
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
                fetch('/admin/reembolsos/admin/' + id + '/processar', {
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
                            text: data.message,
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
                        });
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