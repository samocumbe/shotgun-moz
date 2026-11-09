@extends('layouts.admin')

@section('title', 'Relatório de Vendas - ' . $evento->nome)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório de Vendas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('relatorios.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('relatorios.vendas', $evento->id) }}?exportar=pdf" class="btn btn-sm btn-danger ms-2">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
    </div>
</div>

{{-- Resumo --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Total de Vendas</small>
            <h4>{{ number_format($totais['total_vendas'], 0, ',', '.') }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Valor Total</small>
            <h4>{{ number_format($totais['valor_total'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Valor Médio</small>
            <h4>{{ number_format($totais['valor_medio'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Evento</small>
            <h5>{{ $evento->nome }}</h5>
        </div>
    </div>
</div>

{{-- Tabela de Vendas --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendas as $venda)
                    <tr>
                        <td>{{ $venda->id }}</td>
                        <td>{{ $venda->participante->nome_completo ?? 'N/A' }}</td>
                        <td><strong>{{ number_format($venda->valor_total, 2, ',', '.') }} MZN</strong></td>
                        <td>
                            @if($venda->tipo == 'presencial')
                                <span class="badge bg-primary">Presencial</span>
                            @elseif($venda->tipo == 'online')
                                <span class="badge bg-info">Online</span>
                            @elseif($venda->tipo == 'promoter')
                                <span class="badge bg-warning text-dark">Promoter</span>
                            @endif
                        </td>
                        <td>
                            @if($venda->status == 'confirmada')
                                <span class="badge badge-status badge-published">Confirmada</span>
                            @elseif($venda->status == 'pendente')
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @elseif($venda->status == 'cancelada')
                                <span class="badge badge-status badge-canceled">Cancelada</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Nenhuma venda encontrada para este evento.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection