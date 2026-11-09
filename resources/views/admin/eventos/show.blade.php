{{--
    ============================================================
    VIEW: Detalhes do Evento
    ============================================================
    Exibe todas as informações de um evento, incluindo:
    - Dados básicos do evento
    - Estatísticas (ingressos vendidos, disponíveis, etc)
    - Lista de categorias de ingressos com CRUD completo
    - Ações para gerenciar o evento
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Evento - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Evento</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eventos.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('eventos.edit', $evento->id) }}" class="btn btn-sm btn-warning ms-2">
            <i class="bi bi-pencil"></i> Editar
        </a>
        @if($evento->status == 'rascunho')
        <form action="{{ route('eventos.publicar', $evento->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-success ms-2">
                <i class="bi bi-check"></i> Publicar
            </button>
        </form>
        @endif
    </div>
</div>

<div class="row">
    {{-- ============================================================
    COLUNA PRINCIPAL: Dados do Evento
    ============================================================ --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Evento</h5>
            </div>
            <div class="card-body">
                {{-- Nome e Status --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nome:</strong>
                        <p class="mt-1">{{ $evento->nome }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p class="mt-1">
                            @if($evento->status == 'publicado')
                                <span class="badge badge-status badge-published">Publicado</span>
                            @elseif($evento->status == 'rascunho')
                                <span class="badge badge-status badge-draft">Rascunho</span>
                            @elseif($evento->status == 'encerrado')
                                <span class="badge badge-status badge-ended">Encerrado</span>
                            @elseif($evento->status == 'cancelado')
                                <span class="badge badge-status badge-canceled">Cancelado</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Local --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Local:</strong>
                        <p class="mt-1">{{ $evento->local->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Endereço:</strong>
                        <p class="mt-1">{{ $evento->local->endereco ?? 'N/A' }}</p>
                    </div>
                </div>

                {{-- Datas --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Data Início:</strong>
                        <p class="mt-1">{{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Data Fim:</strong>
                        <p class="mt-1">{{ \Carbon\Carbon::parse($evento->data_fim)->format('d/m/Y') }}</p>
                    </div>
                </div>

                {{-- Horários --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Hora Início:</strong>
                        <p class="mt-1">{{ $evento->hora_inicio }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Hora Fim:</strong>
                        <p class="mt-1">{{ $evento->hora_fim }}</p>
                    </div>
                </div>

                {{-- Capacidade --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Capacidade Total:</strong>
                        <p class="mt-1">{{ number_format($evento->capacidade_total, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Ingressos Vendidos:</strong>
                        <p class="mt-1">{{ number_format($evento->ingressos_vendidos, 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Descrição --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Descrição:</strong>
                        <p class="mt-1">{{ $evento->descricao ?? 'Sem descrição' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
        CATEGORIAS DE INGRESSOS (CRUD COMPLETO)
        ============================================================ --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-ticket"></i> Categorias de Ingressos</h5>
                <a href="{{ route('categorias.create', $evento->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Nova Categoria
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Preço</th>
                                <th>Quantidade</th>
                                <th>Vendidos</th>
                                <th>Disponíveis</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evento->categoriasIngressos as $categoria)
                            <tr>
                                <td>{{ $categoria->id }}</td>
                                <td>{{ $categoria->nome }}</td>
                                <td>
                                    <span class="badge bg-info text-white">
                                        {{ ucfirst($categoria->tipo) }}
                                    </span>
                                </td>
                                <td>{{ number_format($categoria->preco, 2, ',', '.') }} MZN</td>
                                <td>{{ number_format($categoria->quantidade_total, 0, ',', '.') }}</td>
                                <td>{{ number_format($categoria->quantidade_vendida, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $disponiveis = $categoria->quantidade_total - $categoria->quantidade_vendida;
                                    @endphp
                                    <span class="badge {{ $disponiveis > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($disponiveis, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('categorias.show', ['eventoId' => $evento->id, 'id' => $categoria->id]) }}" 
                                       class="btn btn-sm btn-info" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('categorias.edit', ['eventoId' => $evento->id, 'id' => $categoria->id]) }}" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('categorias.destroy', ['eventoId' => $evento->id, 'id' => $categoria->id]) }}" 
                                          method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                title="Excluir"
                                                onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-ticket" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Nenhuma categoria de ingresso cadastrada.</p>
                                    <a href="{{ route('categorias.create', $evento->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus"></i> Adicionar primeira categoria
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
    COLUNA LATERAL: Estatísticas e Ações
    ============================================================ --}}
    <div class="col-md-4">
        {{-- Estatísticas --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estatísticas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Capacidade Disponível</small>
                    <h4>{{ number_format($evento->capacidade_total - $evento->ingressos_vendidos, 0, ',', '.') }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total de Ingressos</small>
                    <h4>{{ number_format($evento->ingressos->count(), 0, ',', '.') }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Checkins Realizados</small>
                    <h4>{{ number_format($evento->checkins->count(), 0, ',', '.') }}</h4>
                </div>
                <div class="progress" style="height: 20px;">
                    @php
                        $percentual = $evento->capacidade_total > 0 
                            ? ($evento->ingressos_vendidos / $evento->capacidade_total) * 100 
                            : 0;
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: {{ $percentual }}%;" 
                         aria-valuenow="{{ $percentual }}" aria-valuemin="0" aria-valuemax="100">
                        {{ number_format($percentual, 1) }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Ações Rápidas --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('ingressos.index', $evento->id) }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-ticket"></i> Gerenciar Ingressos
                </a>
                <a href="{{ route('vendas.create') }}?evento_id={{ $evento->id }}" class="btn btn-success w-100 mb-2">
                    <i class="bi bi-cart"></i> Nova Venda
                </a>
                <a href="{{ route('relatorios.vendas', $evento->id) }}" class="btn btn-info w-100 mb-2">
                    <i class="bi bi-file-earmark-bar-graph"></i> Relatório de Vendas
                </a>
                <a href="{{ route('relatorios.checkins', $evento->id) }}" class="btn btn-secondary w-100">
                    <i class="bi bi-check2-circle"></i> Relatório de Check-ins
                </a>
            </div>
        </div>
    </div>
</div>
@endsection