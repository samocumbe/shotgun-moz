{{--
    ============================================================
    VIEW: Detalhes do Pagamento
    ============================================================
    Exibe todas as informações de um pagamento.
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Pagamento - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Pagamento #{{ $pagamento->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('pagamentos.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {{-- Dados do Pagamento --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Pagamento</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID:</strong> #{{ $pagamento->id }}
                    </div>
                    <div class="col-md-6">
                        <strong>UUID:</strong> <code>{{ $pagamento->uuid }}</code>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Pedido:</strong> 
                        <a href="{{ route('pedidos.show', $pagamento->pedido_id) }}">
                            {{ $pagamento->pedido->numero_pedido ?? 'N/A' }}
                        </a>
                    </div>
                    <div class="col-md-6">
                        <strong>Valor:</strong> 
                        <h4 class="text-success">{{ number_format($pagamento->valor, 2, ',', '.') }} MZN</h4>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Método:</strong>
                        @if($pagamento->metodo == 'mpesa')
                            <span class="badge bg-success">📱 M-Pesa</span>
                        @elseif($pagamento->metodo == 'emola')
                            <span class="badge bg-info">📱 e-Mola</span>
                        @elseif($pagamento->metodo == 'dinheiro')
                            <span class="badge bg-warning text-dark">💰 Dinheiro</span>
                        @elseif($pagamento->metodo == 'cartao')
                            <span class="badge bg-primary">💳 Cartão</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        @if($pagamento->status == 'confirmado')
                            <span class="badge badge-status badge-published">Confirmado</span>
                        @elseif($pagamento->status == 'pendente')
                            <span class="badge badge-status badge-draft">Pendente</span>
                        @elseif($pagamento->status == 'falha')
                            <span class="badge badge-status badge-canceled">Falha</span>
                        @endif
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Data Criação:</strong> 
                        {{ \Carbon\Carbon::parse($pagamento->created_at)->format('d/m/Y H:i') }}
                    </div>
                    <div class="col-md-6">
                        <strong>Data Confirmação:</strong> 
                        {{ $pagamento->data_confirmacao ? \Carbon\Carbon::parse($pagamento->data_confirmacao)->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                </div>
                @if($pagamento->referencia)
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Referência:</strong> {{ $pagamento->referencia }}
                    </div>
                </div>
                @endif
                @if($pagamento->codigo_confirmacao)
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Código de Confirmação:</strong> {{ $pagamento->codigo_confirmacao }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Sidebar --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                @if($pagamento->status == 'pendente')
                    <form action="{{ route('pagamentos.confirmar', $pagamento->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-check"></i> Confirmar Pagamento
                        </button>
                    </form>
                @endif
                <button class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-printer"></i> Imprimir Recibo
                </button>
                <button class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-return-left"></i> Reembolsar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection