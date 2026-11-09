@extends('layouts.admin')

@section('title', 'Eventos Ativos - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-calendar-event text-success"></i> Eventos Ativos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eventos.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

@if($eventos->count() > 0)
<div class="row">
    @foreach($eventos as $evento)
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">{{ $evento->nome }}</h5>
            </div>
            <div class="card-body">
                <p><strong>Local:</strong> {{ $evento->local->nome ?? 'N/A' }}</p>
                <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}</p>
                <p><strong>Capacidade:</strong> {{ number_format($evento->capacidade_total, 0, ',', '.') }}</p>
                <p><strong>Ingressos Vendidos:</strong> {{ number_format($evento->ingressos_vendidos, 0, ',', '.') }}</p>
                <div class="progress">
                    @php
                        $percentual = $evento->capacidade_total > 0 
                            ? ($evento->ingressos_vendidos / $evento->capacidade_total) * 100 
                            : 0;
                    @endphp
                    <div class="progress-bar bg-success" 
                         role="progressbar" 
                         style="width: {{ $percentual }}%;" 
                         aria-valuenow="{{ $percentual }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        {{ number_format($percentual, 1) }}%
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> Ver Detalhes
                </a>
                <a href="{{ route('ingressos.index', ['eventoId' => $evento->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-ticket"></i> Ingressos
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{ $eventos->links() }}
@else
<div class="text-center py-5">
    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
    <h4 class="mt-3 text-muted">Nenhum evento ativo</h4>
    <p class="text-muted">No momento não há eventos publicados e com datas futuras.</p>
    <a href="{{ route('eventos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> Criar Evento
    </a>
</div>
@endif
@endsection