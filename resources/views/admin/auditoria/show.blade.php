{{--
    ============================================================
    VIEW: Detalhes do Log
    ============================================================
    Exibe todas as informações de um log específico.
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Log - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clipboard-data text-primary"></i> Detalhes do Log</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {{-- Informações do Log --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Log</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID:</strong> #{{ $log->id }}
                    </div>
                    <div class="col-md-6">
                        <strong>UUID:</strong> <code>{{ $log->uuid }}</code>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Usuário:</strong> 
                        {{ $log->user->name ?? 'Sistema' }}
                        @if($log->user)
                            <br>
                            <small class="text-muted">{{ $log->user->email }}</small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Organização:</strong> 
                        {{ $log->organizacao->nome ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Ação:</strong>
                        <i class="{{ $log->acao_icon }}"></i>
                        <span class="badge bg-{{ $log->acao == 'create' ? 'success' : ($log->acao == 'update' ? 'warning' : ($log->acao == 'delete' ? 'danger' : 'info')) }}">
                            {{ $log->acao_label }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Tabela:</strong> 
                        <span class="badge bg-secondary">{{ ucfirst($log->tabela) }}</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Registro ID:</strong> <code>#{{ $log->registro_id }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Data/Hora:</strong> 
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                        <br>
                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>IP:</strong> <code>{{ $log->ip ?? 'N/A' }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Método HTTP:</strong> 
                        <span class="badge bg-secondary">{{ $log->metodo ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Rota:</strong> 
                        <code>{{ $log->rota ?? 'N/A' }}</code>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>User Agent:</strong>
                        <br>
                        <small class="text-muted">{{ $log->user_agent ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dados Anteriores --}}
        @if($log->dados_anteriores)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left text-warning"></i> Dados Anteriores</h5>
            </div>
            <div class="card-body">
                <pre style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 300px; overflow-y: auto;">
                    {{ json_encode($log->dados_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                </pre>
            </div>
        </div>
        @endif

        {{-- Dados Novos --}}
        @if($log->dados_novos)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-right text-success"></i> Dados Novos</h5>
            </div>
            <div class="card-body">
                <pre style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 300px; overflow-y: auto;">
                    {{ json_encode($log->dados_novos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                </pre>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                <button onclick="window.print()" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <button onclick="voltar()" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Voltar
                </button>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-tag"></i> Tags</h5>
            </div>
            <div class="card-body">
                <span class="badge bg-secondary me-1">#{{ $log->id }}</span>
                <span class="badge bg-primary me-1">{{ $log->acao }}</span>
                <span class="badge bg-info me-1">{{ $log->tabela }}</span>
                <span class="badge bg-dark">{{ $log->created_at->format('Y-m-d') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function voltar() {
        window.history.back();
    }
</script>
@endpush