{{--
    ============================================================
    VIEW: Comissões do Promoter
    ============================================================
    Exibe todas as comissões geradas por um promoter.
--}}

@extends('layouts.admin')

@section('title', 'Comissões - ' . $promoter->nome . ' - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Comissões de {{ $promoter->nome }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('promoters.show', $promoter->id) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

{{-- Resumo do Promoter --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Código</small>
            <h5><code>{{ $promoter->codigo_promocional }}</code></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Comissão Padrão</small>
            <h5>{{ number_format($promoter->comissao_padrao, 0) }}%</h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Total de Comissões</small>
            <h5>{{ number_format($promoter->total_comissao, 2, ',', '.') }} MZN</h5>
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

{{-- Tabela de Comissões --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pedido</th>
                        <th>Venda</th>
                        <th>Valor</th>
                        <th>% Comissão</th>
                        <th>Comissão</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comissoes as $comissao)
                    <tr>
                        <td>{{ $comissao->id }}</td>
                        <td>
                            <a href="{{ route('pedidos.show', $comissao->pedido_id) }}">
                                {{ $comissao->pedido->numero_pedido ?? 'N/A' }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('vendas.show', $comissao->venda_id) }}">
                                Venda #{{ $comissao->venda_id }}
                            </a>
                        </td>
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
                        <td>
                            @if($comissao->status == 'pendente')
                                <form action="{{ route('promoters.comissoes.pagar', $comissao->id) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Pagar">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-coin" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhuma comissão registrada para este promoter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <td colspan="5" class="text-end"><strong>Total:</strong></td>
                        <td colspan="4"><strong>{{ number_format($comissoes->sum('valor_comissao'), 2, ',', '.') }} MZN</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        {{ $comissoes->links() }}
    </div>
</div>
@endsection