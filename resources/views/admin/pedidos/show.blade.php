{{--
    ============================================================
    VIEW: Detalhes do Pedido
    ============================================================
    Exibe todas as informações de um pedido:
    - Dados do pedido
    - Dados do cliente
    - Itens do pedido (ingressos)
    - Pagamentos
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Pedido - Shotgun MOZ')

@push('styles')
<style>
    .main-content {
        overflow-x: auto !important;
    }
    .table-responsive {
        overflow-x: auto !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Pedido #{{ $pedido->numero_pedido }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('pedidos.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    {{-- Coluna Principal --}}
    <div class="col-md-8">
        
        {{-- Dados do Pedido --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Pedido</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Número:</strong> {{ $pedido->numero_pedido }}
                    </div>
                    <div class="col-md-6">
                        <strong>Data:</strong> {{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Evento:</strong> {{ $pedido->evento->nome ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Tipo:</strong>
                        @if($pedido->tipo_venda == 'presencial')
                            <span class="badge bg-primary">Presencial</span>
                        @elseif($pedido->tipo_venda == 'online')
                            <span class="badge bg-info">Online</span>
                        @elseif($pedido->tipo_venda == 'promoter')
                            <span class="badge bg-warning text-dark">Promoter</span>
                        @endif
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Subtotal:</strong> {{ number_format($pedido->subtotal, 2, ',', '.') }} MZN
                    </div>
                    <div class="col-md-6">
                        <strong>Total:</strong> {{ number_format($pedido->total, 2, ',', '.') }} MZN
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        @if($pedido->status == 'pago')
                            <span class="badge badge-status badge-published">Pago</span>
                        @elseif($pedido->status == 'pendente')
                            <span class="badge badge-status badge-draft">Pendente</span>
                        @elseif($pedido->status == 'cancelado')
                            <span class="badge badge-status badge-canceled">Cancelado</span>
                        @elseif($pedido->status == 'reembolsado')
                            <span class="badge badge-status badge-ended">Reembolsado</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Vendedor:</strong> {{ $pedido->user->name ?? 'N/A' }}
                    </div>
                </div>
                @if($pedido->observacoes)
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Observações:</strong> {{ $pedido->observacoes }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Cliente --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nome:</strong> {{ $pedido->participante->nome_completo ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong> {{ $pedido->participante->email ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Telefone:</strong> {{ $pedido->participante->telefone ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>NUIT:</strong> {{ $pedido->participante->nuit ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Itens do Pedido --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-ticket"></i> Itens do Pedido</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Categoria</th>
                                <th>QR Code</th>
                                <th>Preço</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedido->itens as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->categoria->nome ?? 'N/A' }}</td>
                                <td>
                                    @if($item->ingresso)
                                        <code>{{ $item->ingresso->qr_code ?? 'N/A' }}</code>
                                    @else
                                        <span class="text-muted">Pendente</span>
                                    @endif
                                </td>
                                <td>{{ number_format($item->preco_unitario, 2, ',', '.') }} MZN</td>
                                <td>
                                    @if($item->ingresso)
                                        @if($item->ingresso->status == 'pago')
                                            <span class="badge badge-status badge-published">Pago</span>
                                        @elseif($item->ingresso->status == 'utilizado')
                                            <span class="badge badge-status badge-ended">Utilizado</span>
                                        @elseif($item->ingresso->status == 'cancelado')
                                            <span class="badge badge-status badge-canceled">Cancelado</span>
                                        @endif
                                    @else
                                        <span class="badge badge-status badge-draft">Pendente</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Nenhum item encontrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td colspan="2"><strong>{{ number_format($pedido->total, 2, ',', '.') }} MZN</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Sidebar --}}
    <div class="col-md-4">
        
        {{-- Pagamentos --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Pagamentos</h5>
            </div>
            <div class="card-body">
                @forelse($pedido->pagamentos ?? [] as $pagamento)
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <span>{{ ucfirst($pagamento->metodo) }}</span>
                        <span class="fw-bold">{{ number_format($pagamento->valor, 2, ',', '.') }} MZN</span>
                    </div>
                    <div>
                        <small class="text-muted">Status:</small>
                        @if($pagamento->status == 'confirmado')
                            <span class="badge badge-status badge-published">Confirmado</span>
                        @elseif($pagamento->status == 'pendente')
                            <span class="badge badge-status badge-draft">Pendente</span>
                        @elseif($pagamento->status == 'falha')
                            <span class="badge badge-status badge-canceled">Falha</span>
                        @endif
                    </div>
                    @if($pagamento->referencia)
                        <div><small>Ref: {{ $pagamento->referencia }}</small></div>
                    @endif
                </div>
                @empty
                <p class="text-muted">Nenhum pagamento registrado</p>
                @endforelse
            </div>
        </div>
        
        {{-- Ações --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-printer"></i> Imprimir Recibo
                </button>
                <button class="btn btn-success w-100 mb-2">
                    <i class="bi bi-envelope"></i> Enviar Email
                </button>
                <button class="btn btn-info w-100">
                    <i class="bi bi-qr-code"></i> Ver QR Codes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection