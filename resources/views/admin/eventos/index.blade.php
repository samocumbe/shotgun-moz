{{--
    ============================================================
    VIEW: Lista de Eventos
    ============================================================
    Exibe todos os eventos cadastrados com opções de:
    - Criar novo evento
    - Visualizar detalhes
    - Editar
    - Duplicar (Multi-Eventos)
    - Salvar como Template (Multi-Eventos)
    - Gerar Recorrentes (Multi-Eventos)
    - Publicar (se for rascunho)
    - Excluir
    
    🔥 Multi-Organizador:
    - Filtro por organização (apenas admin)
    - Exibe organização do evento
--}}

@extends('layouts.admin')

@section('title', 'Eventos - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Eventos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eventos.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Novo Evento
        </a>
        <a href="{{ route('eventos.templates') }}" class="btn btn-sm btn-info ms-2">
            <i class="bi bi-bookmark"></i> Templates
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('eventos.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="rascunho" {{ request('status') == 'rascunho' ? 'selected' : '' }}>Rascunho</option>
                    <option value="publicado" {{ request('status') == 'publicado' ? 'selected' : '' }}>Publicado</option>
                    <option value="encerrado" {{ request('status') == 'encerrado' ? 'selected' : '' }}>Encerrado</option>
                    <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Nome do evento..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Recorrente</label>
                <select name="recorrente" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('recorrente') == '1' ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ request('recorrente') == '0' ? 'selected' : '' }}>Não</option>
                </select>
            </div>
            
            {{-- 🔥 MULTI-ORGANIZADOR: Filtro por organização (apenas admin) --}}
            @if(auth()->user()->isAdmin() && isset($organizacoes) && $organizacoes->count() > 0)
            <div class="col-md-2">
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
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('eventos.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Eventos --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Evento</th>
                        <th>Local</th>
                        <th>Data</th>
                        <th>Ingressos</th>
                        <th>Status</th>
                        <th>Recorrência</th>
                        @if(auth()->user()->isAdmin())
                            <th>Organização</th>
                        @endif
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventos as $evento)
                    <tr>
                        <td>{{ $evento->id }}</td>
                        <td>
                            {{ $evento->nome }}
                            @if($evento->is_template)
                                <span class="badge bg-primary ms-1"><i class="bi bi-bookmark"></i> Template</span>
                            @endif
                            @if($evento->isRecorrente())
                                <span class="badge bg-info ms-1"><i class="bi bi-arrow-repeat"></i> Recorrente</span>
                            @endif
                        </td>
                        <td>{{ $evento->local->nome ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}</td>
                        <td>{{ $evento->ingressos_vendidos }}/{{ $evento->capacidade_total }}</td>
                        <td>
                            @if($evento->status == 'publicado')
                                <span class="badge badge-status badge-published">
                                    <i class="bi bi-check-circle"></i> Publicado
                                </span>
                            @elseif($evento->status == 'rascunho')
                                <span class="badge badge-status badge-draft">
                                    <i class="bi bi-pencil"></i> Rascunho
                                </span>
                            @elseif($evento->status == 'encerrado')
                                <span class="badge badge-status badge-ended">
                                    <i class="bi bi-clock"></i> Encerrado
                                </span>
                            @elseif($evento->status == 'cancelado')
                                <span class="badge badge-status badge-canceled">
                                    <i class="bi bi-x-circle"></i> Cancelado
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($evento->recorrencia == 'nenhuma')
                                <span class="badge bg-secondary">Nenhuma</span>
                            @else
                                <span class="badge bg-info">{{ ucfirst($evento->recorrencia) }}</span>
                            @endif
                        </td>
                        @if(auth()->user()->isAdmin())
                            <td>
                                <span class="badge bg-secondary">{{ $evento->organizacao->nome ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td>
                            {{-- Botão Visualizar --}}
                            <a href="{{ route('eventos.show', $evento->id) }}" 
                               class="btn btn-sm btn-info" title="Visualizar detalhes">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            {{-- Botão Editar --}}
                            <a href="{{ route('eventos.edit', $evento->id) }}" 
                               class="btn btn-sm btn-warning" title="Editar evento">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            {{-- Botão Duplicar --}}
                            <button onclick="duplicarEvento({{ $evento->id }}, '{{ $evento->nome }}')" 
                                    class="btn btn-sm btn-secondary" title="Duplicar evento">
                                <i class="bi bi-copy"></i>
                            </button>
                            
                            {{-- Botão Salvar como Template --}}
                            @if(!$evento->is_template)
                                <button onclick="salvarTemplate({{ $evento->id }})" 
                                        class="btn btn-sm btn-primary" title="Salvar como template">
                                    <i class="bi bi-bookmark"></i>
                                </button>
                            @endif
                            
                            {{-- Botão Publicar --}}
                            @if($evento->status == 'rascunho')
                                <form action="{{ route('eventos.publicar', $evento->id) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Publicar evento">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                            @endif
                            
                            {{-- Botão Gerar Recorrentes --}}
                            @if($evento->isRecorrente() && $evento->status != 'cancelado')
                                <form action="{{ route('eventos.gerar-recorrentes', $evento->id) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info" title="Gerar eventos recorrentes">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            @endif
                            
                            {{-- Botão Excluir --}}
                            <button onclick="confirmDelete({{ $evento->id }}, '{{ $evento->nome }}')" 
                                    class="btn btn-sm btn-danger" title="Excluir evento">
                                <i class="bi bi-trash"></i>
                            </button>
                            
                            <form id="delete-form-{{ $evento->id }}" 
                                  action="{{ route('eventos.destroy', $evento->id) }}" 
                                  method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 10 : 9 }}" class="text-center text-muted py-4">
                            <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhum evento cadastrado.</p>
                            <a href="{{ route('eventos.create') }}" class="btn btn-sm btn-primary">
                                Criar primeiro evento
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Paginação --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $eventos->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id, nome) {
        Swal.fire({
            title: 'Tem certeza?',
            html: `Deseja realmente excluir o evento <strong>"${nome}"</strong>?<br><br>Esta ação não pode ser desfeita.`,
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

    function duplicarEvento(id, nome) {
        Swal.fire({
            title: 'Duplicar evento?',
            html: `Deseja duplicar o evento <strong>"${nome}"</strong>?<br><br>Todas as categorias serão copiadas.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="bi bi-copy"></i> Sim, duplicar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Duplicando...',
                    text: 'Aguarde enquanto o evento é duplicado.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('/admin/eventos/' + id + '/duplicar', {
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
                            title: 'Duplicado!',
                            text: data.message,
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Erro ao duplicar');
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

    function salvarTemplate(id) {
        Swal.fire({
            title: 'Salvar como template?',
            text: 'Este evento será salvo como template para criar outros eventos.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-bookmark"></i> Sim, salvar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Salvando...',
                    text: 'Aguarde.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('/admin/eventos/' + id + '/template', {
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
                            title: 'Template salvo!',
                            text: data.message,
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Erro ao salvar');
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