@extends('layouts.admin')

@section('title', 'Detalhes do Local - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Local</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('locais.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $local->nome }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Endereço:</strong> {{ $local->endereco }}
                    </div>
                    <div class="col-md-6">
                        <strong>Cidade:</strong> {{ $local->cidade }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Província:</strong> {{ $local->provincia }}
                    </div>
                    <div class="col-md-6">
                        <strong>Capacidade:</strong> {{ number_format($local->capacidade, 0, ',', '.') }} pessoas
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Descrição:</strong>
                        <p>{{ $local->descricao ?? 'Sem descrição' }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <strong>Total de Eventos:</strong> 
                        <span class="badge bg-primary">{{ $local->eventos->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Estatísticas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total de Eventos</small>
                    <h4>{{ $local->eventos->count() }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Eventos Ativos</small>
                    <h4>{{ $local->eventos->where('status', 'publicado')->count() }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Eventos Encerrados</small>
                    <h4>{{ $local->eventos->where('status', 'encerrado')->count() }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Capacidade Total</small>
                    <h4>{{ number_format($local->capacidade, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

@if($local->eventos->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Eventos neste Local</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Evento</th>
                                <th>Data</th>
                                <th>Ingressos</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($local->eventos as $evento)
                            <tr>
                                <td>{{ $evento->id }}</td>
                                <td>{{ $evento->nome }}</td>
                                <td>{{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}</td>
                                <td>{{ $evento->ingressos_vendidos }}/{{ $evento->capacidade_total }}</td>
                                <td>
                                    @if($evento->status == 'publicado')
                                        <span class="badge badge-status badge-published">Publicado</span>
                                    @elseif($evento->status == 'rascunho')
                                        <span class="badge badge-status badge-draft">Rascunho</span>
                                    @elseif($evento->status == 'encerrado')
                                        <span class="badge badge-status badge-ended">Encerrado</span>
                                    @elseif($evento->status == 'cancelado')
                                        <span class="badge badge-status badge-canceled">Cancelado</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection