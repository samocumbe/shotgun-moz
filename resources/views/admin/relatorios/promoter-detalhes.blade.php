@extends('layouts.admin')

@section('title', 'Relatório de ' . $promoter->nome)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório do Promoter: {{ $promoter->nome }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('relatorios.promoters') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('relatorios.promoter-detalhes', $promoter->id) }}?exportar=pdf" class="btn btn-sm btn-danger ms-2">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
    </div>
</div>

{{-- Dados do Promoter --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Nome</small>
            <h5>{{ $promoter->nome }}</h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Email</small>
            <h5>{{ $promoter->email }}</h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Código</small>
            <h5><code>{{ $promoter->codigo_promocional }}</code></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Status</small>
            <h5>
                @if($promoter->status == 'ativo')
                    <span class="badge badge-status badge-published">Ativo</span>
                @elseif($promoter->status == 'inativo')
                    <span class="badge badge-status badge-canceled">Inativo</span>
                @elseif($promoter->status == 'pendente')
                    <span class="badge badge-status badge-draft">Pendente</span>
                @endif
            </h5>
        </div>
    </div>
</div>

{{-- Resumo Financeiro --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Total de Vendas</small>
            <h4>{{ number_format($resumo['total_vendas'], 0, ',', '.') }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Valor Total</small>
            <h4>{{ number_format($resumo['valor_total'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Comissão Total</small>
            <h4 class="text-success">{{ number_format($resumo['comissao_total'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Comissão Padrão</small>
            <h4>{{ number_format($promoter->comissao_padrao, 0) }}%</h4>
        </div>
    </div>
</div>

{{-- Vendas por Evento --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Vendas por Evento</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Quantidade</th>
                        <th>Valor</th>
                        <th>Comissão</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendas_por_evento as $venda)
                    <tr>
                        <td>{{ $venda['evento'] }}</td>
                        <td>{{ $venda['quantidade'] }}</td>
                        <td>{{ number_format($venda['valor'], 2, ',', '.') }} MZN</td>
                        <td>{{ number_format($venda['comissao'], 2, ',', '.') }} MZN</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Nenhuma venda encontrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Lista de Comissões --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Histórico de Comissões</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pedido</th>
                        <th>Valor Venda</th>
                        <th>% Comissão</th>
                        <th>Comissão</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comissoes as $comissao)
                    <tr>
                        <td>{{ $comissao->id }}</td>
                        <td>{{ $comissao->pedido->numero_pedido ?? 'N/A' }}</td>
                        <td>{{ number_format($comissao->valor_venda, 2, ',', '.') }} MZN</td>
                        <td>{{ number_format($comissao->percentual_comissao, 0) }}%</td>
                        <td><strong>{{ number_format($comissao->valor_comissao, 2, ',', '.') }} MZN</strong></td>
                        <td>
                            @if($comissao->status == 'pago')
                                <span class="badge badge-status badge-published">Pago</span>
                            @elseif($comissao->status == 'pendente')
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @elseif($comissao->status == 'cancelado')
                                <span class="badge badge-status badge-canceled">Cancelado</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($comissao->created_at)->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Nenhuma comissão registrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection