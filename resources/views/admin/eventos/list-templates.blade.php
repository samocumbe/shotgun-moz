{{--
    ============================================================
    VIEW: Templates de Eventos
    ============================================================
    Exibe todos os eventos marcados como template.
    Permite criar novos eventos a partir dos templates.
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Templates de Eventos - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-bookmark text-primary"></i> Templates de Eventos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eventos.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('eventos.create') }}" class="btn btn-sm btn-primary ms-2">
            <i class="bi bi-plus"></i> Novo Evento
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('eventos.templates') }}">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nome do template..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('eventos.templates') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Estatísticas --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="number text-primary">{{ $templates->total() ?? 0 }}</div>
            <div class="label">Total de Templates</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="number text-success">{{ $templates->sum('capacidade_total') ?? 0 }}</div>
            <div class="label">Capacidade Total</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="number text-warning">{{ $templates->sum('categoriasIngressos_count') ?? 0 }}</div>
            <div class="label">Total de Categorias</div>
        </div>
    </div>
</div>

{{-- Lista de Templates --}}
<div class="row">
    @forelse($templates as $template)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm hover-shadow-lg transition-all">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-truncate" title="{{ $template->nome }}">
                        <i class="bi bi-bookmark"></i> {{ $template->nome }}
                    </h5>
                    <span class="badge bg-light text-dark">Template</span>
                </div>
            </div>
            <div class="card-body">
                {{-- Local --}}
                <p class="mb-1">
                    <small class="text-muted"><i class="bi bi-geo-alt"></i> Local</small>
                    <br>
                    <strong>{{ $template->local->nome ?? 'N/A' }}</strong>
                </p>
                
                {{-- Data --}}
                <p class="mb-1">
                    <small class="text-muted"><i class="bi bi-calendar"></i> Data</small>
                    <br>
                    <strong>{{ \Carbon\Carbon::parse($template->data_inicio)->format('d/m/Y') }}</strong>
                </p>
                
                {{-- Capacidade --}}
                <p class="mb-1">
                    <small class="text-muted"><i class="bi bi-people"></i> Capacidade</small>
                    <br>
                    <strong>{{ number_format($template->capacidade_total, 0, ',', '.') }} pessoas</strong>
                </p>
                
                {{-- Categorias --}}
                <p class="mb-1">
                    <small class="text-muted"><i class="bi bi-ticket"></i> Categorias</small>
                    <br>
                    <strong>{{ $template->categoriasIngressos->count() }}</strong> categorias
                </p>
                
                {{-- Status --}}
                <p class="mb-1">
                    <small class="text-muted"><i class="bi bi-check-circle"></i> Status Original</small>
                    <br>
                    @if($template->status == 'publicado')
                        <span class="badge bg-success">Publicado</span>
                    @elseif($template->status == 'rascunho')
                        <span class="badge bg-secondary">Rascunho</span>
                    @elseif($template->status == 'encerrado')
                        <span class="badge bg-info">Encerrado</span>
                    @elseif($template->status == 'cancelado')
                        <span class="badge bg-danger">Cancelado</span>
                    @endif
                </p>
                
                {{-- Data de criação --}}
                <p class="mb-0">
                    <small class="text-muted"><i class="bi bi-clock"></i> Criado em</small>
                    <br>
                    <small>{{ $template->created_at->format('d/m/Y H:i') }}</small>
                </p>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-grid gap-2">
                    {{-- Botão Usar Template --}}
                    <a href="{{ route('eventos.criar-template', $template->id) }}" 
                       class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Usar Template
                    </a>
                    
                    {{-- Botões adicionais --}}
                    <div class="d-flex gap-2">
                        <a href="{{ route('eventos.show', $template->id) }}" 
                           class="btn btn-sm btn-info flex-grow-1" title="Visualizar">
                            <i class="bi bi-eye"></i> Detalhes
                        </a>
                        <a href="{{ route('eventos.edit', $template->id) }}" 
                           class="btn btn-sm btn-warning flex-grow-1" title="Editar">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                    </div>
                    <button onclick="desativarTemplate({{ $template->id }})" 
                            class="btn btn-sm btn-danger w-100" title="Remover template">
                        <i class="bi bi-x-circle"></i> Remover Template
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5">
            <i class="bi bi-bookmark text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">Nenhum template encontrado</h4>
            <p class="text-muted">
                Salve um evento como template para reutilizá-lo futuramente.
            </p>
            <div class="mt-3">
                <a href="{{ route('eventos.index') }}" class="btn btn-primary me-2">
                    <i class="bi bi-calendar-event"></i> Ver Eventos
                </a>
                <button onclick="criarTemplateExemplo()" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle"></i> Criar Template Exemplo
                </button>
            </div>
        </div>
    </div>
    @endforelse
</div>

{{-- Paginação --}}
@if($templates->count() > 0)
<div class="d-flex justify-content-center mt-3">
    {{ $templates->appends(request()->query())->links() }}
</div>
@endif
@endsection

@push('styles')
<style>
    .hover-shadow-lg {
        transition: all 0.3s ease;
    }
    .hover-shadow-lg:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .card-header {
        border-bottom: none;
    }
    .stat-card {
        padding: 20px;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        height: 100%;
    }
    .stat-card .number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a2332;
    }
    .stat-card .label {
        color: #7f8c8d;
        font-size: 0.85rem;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
    /**
     * ============================================================
     * DESATIVAR TEMPLATE
     * ============================================================
     * Remove a marcação de template de um evento
     */
    function desativarTemplate(id) {
        Swal.fire({
            title: 'Remover template?',
            text: 'Deseja remover a marcação de template deste evento? O evento continuará existindo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-x-circle"></i> Sim, remover',
            cancelButtonText: '<i class="bi bi-x"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Removendo...',
                    text: 'Aguarde.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('/admin/eventos/' + id + '/desativar-template', {
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
                            title: 'Removido!',
                            text: data.message,
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Erro ao remover template');
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

    /**
     * ============================================================
     * CRIAR TEMPLATE EXEMPLO
     * ============================================================
     * Cria um template de exemplo para demonstração
     */
    function criarTemplateExemplo() {
        Swal.fire({
            title: 'Criar template exemplo?',
            text: 'Isso criará um evento de exemplo marcado como template.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-plus-circle"></i> Sim, criar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Criando...',
                    text: 'Aguarde.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // Redirecionar para criação de evento com template marcado
                window.location.href = "{{ route('eventos.create') }}?template=1";
            }
        });
    }
</script>
@endpush