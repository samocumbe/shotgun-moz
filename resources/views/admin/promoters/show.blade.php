{{--
    ============================================================
    VIEW: Detalhes do Promoter
    ============================================================
    Exibe todas as informações de um promoter, incluindo
    estatísticas de vendas e comissões.
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Promoter - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Promoter</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('promoters.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('promoters.edit', $promoter->id) }}" class="btn btn-sm btn-warning ms-2">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('promoters.comissoes', $promoter->id) }}" class="btn btn-sm btn-success ms-2">
            <i class="bi bi-coin"></i> Comissões
        </a>
    </div>
</div>

<div class="row">
    {{-- Coluna Principal --}}
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Dados Pessoais</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nome:</strong> {{ $promoter->nome }}
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong> {{ $promoter->email }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Telefone:</strong> {{ $promoter->telefone }}
                    </div>
                    <div class="col-md-6">
                        <strong>NUIT:</strong> {{ $promoter->nuit ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        @if($promoter->status == 'ativo')
                            <span class="badge badge-status badge-published">Ativo</span>
                        @elseif($promoter->status == 'inativo')
                            <span class="badge badge-status badge-canceled">Inativo</span>
                        @elseif($promoter->status == 'pendente')
                            <span class="badge badge-status badge-draft">Pendente</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Comissão Padrão:</strong> {{ number_format($promoter->comissao_padrao, 0) }}%
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-ticket"></i> Informações de Venda</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Código Promocional:</strong> 
                        <code>{{ $promoter->codigo_promocional }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Link Personalizado:</strong> 
                        <code>{{ $promoter->link_personalizado }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Sidebar Estatísticas --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estatísticas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total de Vendas</small>
                    <h4>{{ number_format($estatisticas['total_vendas'] ?? 0, 0, ',', '.') }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Valor Total Vendido</small>
                    <h4>{{ number_format($estatisticas['total_ingressos'] ?? 0, 2, ',', '.') }} MZN</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Comissão Total</small>
                    <h4 class="text-success">{{ number_format($estatisticas['total_comissao'] ?? 0, 2, ',', '.') }} MZN</h4>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">Vendas por Evento</small>
                    @forelse($estatisticas['vendas_por_evento'] ?? [] as $evento)
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <span>{{ $evento['evento'] }}</span>
                        <span class="fw-bold">{{ number_format($evento['total'], 0) }}</span>
                    </div>
                    @empty
                    <p class="text-muted">Nenhuma venda registrada</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection