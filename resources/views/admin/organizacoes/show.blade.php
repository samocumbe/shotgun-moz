{{--
    ============================================================
    VIEW: Detalhes da Organização
    ============================================================
    Exibe todas as informações de uma organização:
    - Dados da organização
    - Lista de usuários da organização
    - Lista de eventos da organização
    - Estatísticas
--}}

@extends('layouts.admin')

@section('title', 'Detalhes da Organização - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-building text-primary"></i> Detalhes da Organização</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('organizacoes.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('organizacoes.edit', $organizacao->id) }}" class="btn btn-sm btn-warning ms-2">
            <i class="bi bi-pencil"></i> Editar
        </a>
    </div>
</div>

<div class="row">
    {{-- COLUNA ESQUERDA - DADOS DA ORGANIZAÇÃO --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
            </div>
            <div class="card-body">
                {{-- Logo --}}
                @if($organizacao->logo)
                    <div class="text-center mb-3">
                        <img src="{{ asset('storage/' . $organizacao->logo) }}" 
                             alt="Logo" style="max-height: 100px;">
                    </div>
                @endif

                {{-- Nome --}}
                <div class="mb-2">
                    <strong>Nome:</strong> {{ $organizacao->nome }}
                </div>

                {{-- Email --}}
                <div class="mb-2">
                    <strong>Email:</strong> {{ $organizacao->email ?? 'N/A' }}
                </div>

                {{-- Telefone --}}
                <div class="mb-2">
                    <strong>Telefone:</strong> {{ $organizacao->telefone ?? 'N/A' }}
                </div>

                {{-- Website --}}
                @if($organizacao->website)
                    <div class="mb-2">
                        <strong>Website:</strong> 
                        <a href="{{ $organizacao->website }}" target="_blank">{{ $organizacao->website }}</a>
                    </div>
                @endif

                {{-- Endereço --}}
                @if($organizacao->endereco)
                    <div class="mb-2">
                        <strong>Endereço:</strong><br>
                        {{ $organizacao->endereco }}
                    </div>
                @endif

                {{-- Descrição --}}
                @if($organizacao->descricao)
                    <div class="mb-2">
                        <strong>Descrição:</strong>
                        <p class="mt-1">{{ $organizacao->descricao }}</p>
                    </div>
                @endif

                {{-- Status --}}
                <div class="mb-2">
                    <strong>Status:</strong>
                    @if($organizacao->status == 'ativo')
                        <span class="badge bg-success">Ativo</span>
                    @elseif($organizacao->status == 'inativo')
                        <span class="badge bg-danger">Inativo</span>
                    @elseif($organizacao->status == 'suspenso')
                        <span class="badge bg-warning text-dark">Suspenso</span>
                    @endif
                </div>

                {{-- Datas --}}
                <div class="mb-2">
                    <strong>Criado em:</strong> {{ $organizacao->created_at->format('d/m/Y H:i') }}
                </div>
                <div class="mb-2">
                    <strong>Atualizado em:</strong> {{ $organizacao->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    {{-- COLUNA DIREITA - ESTATÍSTICAS E LISTAS --}}
    <div class="col-md-8">
        {{-- Estatísticas --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="number text-info">{{ $organizacao->usuarios->count() }}</div>
                    <div class="label">Total de Usuários</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="number text-success">{{ $organizacao->eventos->count() }}</div>
                    <div class="label">Total de Eventos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="number text-warning">{{ $organizacao->eventos->sum('ingressos_vendidos') }}</div>
                    <div class="label">Ingressos Vendidos</div>
                </div>
            </div>
        </div>

        {{-- Lista de Usuários --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Usuários da Organização</h5>
            </div>
            <div class="card-body">
                @if($organizacao->usuarios->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Papel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($organizacao->usuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $usuario->role_color }}">
                                            {{ $usuario->role_name }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Nenhum usuário vinculado a esta organização.</p>
                @endif
            </div>
        </div>

        {{-- Lista de Eventos --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Eventos da Organização</h5>
            </div>
            <div class="card-body">
                @if($organizacao->eventos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Local</th>
                                    <th>Data</th>
                                    <th>Ingressos</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($organizacao->eventos as $evento)
                                <tr>
                                    <td>{{ $evento->nome }}</td>
                                    <td>{{ $evento->local->nome ?? 'N/A' }}</td>
                                    <td>{{ $evento->getDataInicioFormatada() }}</td>
                                    <td>{{ $evento->ingressos_vendidos }}/{{ $evento->capacidade_total }}</td>
                                    <td>
                                        @if($evento->status == 'publicado')
                                            <span class="badge bg-success">Publicado</span>
                                        @elseif($evento->status == 'rascunho')
                                            <span class="badge bg-secondary">Rascunho</span>
                                        @elseif($evento->status == 'encerrado')
                                            <span class="badge bg-info">Encerrado</span>
                                        @elseif($evento->status == 'cancelado')
                                            <span class="badge bg-danger">Cancelado</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Nenhum evento vinculado a esta organização.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection