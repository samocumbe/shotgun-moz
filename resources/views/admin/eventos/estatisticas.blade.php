{{--
    ============================================================
    VIEW: Estatísticas do Evento
    ============================================================
    Exibe estatísticas detalhadas de um evento.
--}}

@extends('layouts.admin')

@section('title', 'Estatísticas - ' . $evento->nome)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-bar-chart text-primary"></i> Estatísticas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    {{-- Cards de Estatísticas --}}
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number">{{ $totalVendas ?? 0 }}</div>
            <div class="label">Ingressos Vendidos</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-success">{{ number_format($totalReceita ?? 0, 2, ',', '.') }} MZN</div>
            <div class="label">Receita Total</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-info">{{ $totalCheckins ?? 0 }}</div>
            <div class="label">Check-ins Realizados</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="number text-warning">{{ $evento->getCapacidadeDisponivel() }}</div>
            <div class="label">Ingressos Disponíveis</div>
        </div>
    </div>
</div>

{{-- Vendas por Categoria --}}
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-ticket"></i> Vendas por Categoria</h5>
    </div>
    <div class="card-body">
        @if(isset($vendasPorCategoria) && $vendasPorCategoria->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Quantidade</th>
                            <th>Receita</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendasPorCategoria as $categoria)
                        <tr>
                            <td>{{ $categoria['nome'] }}</td>
                            <td>{{ $categoria['total'] }}</td>
                            <td>{{ number_format($categoria['receita'], 2, ',', '.') }} MZN</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">Nenhuma venda registrada.</p>
        @endif
    </div>
</div>
@endsection