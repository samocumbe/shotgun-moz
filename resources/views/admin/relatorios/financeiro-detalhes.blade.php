@extends('layouts.admin')

@section('title', 'Relatório Financeiro - ' . $evento->nome)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório Financeiro</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('relatorios.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('relatorios.financeiro', $evento->id) }}?exportar=pdf" class="btn btn-sm btn-danger ms-2">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
    </div>
</div>

{{-- Resumo Financeiro --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Receita Bruta</small>
            <h4 class="text-success">{{ number_format($resumo['receita_bruta'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Total Comissões</small>
            <h4 class="text-danger">{{ number_format($resumo['total_comissoes'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Receita Líquida</small>
            <h4 class="text-primary">{{ number_format($resumo['receita_liquida'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Total Vendas</small>
            <h4>{{ number_format($resumo['total_vendas'], 0, ',', '.') }}</h4>
        </div>
    </div>
</div>

{{-- Comissões --}}
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Comissões</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Pagas</small>
                        <h4 class="text-success">{{ number_format($resumo['comissoes_pagas'], 2, ',', '.') }} MZN</h4>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Pendentes</small>
                        <h4 class="text-warning">{{ number_format($resumo['comissoes_pendentes'], 2, ',', '.') }} MZN</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Métodos de Pagamento</h5>
            </div>
            <div class="card-body">
                @foreach($resumo['por_metodo'] as $metodo => $valor)
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ ucfirst($metodo) }}</span>
                    <span class="fw-bold">{{ number_format($valor, 2, ',', '.') }} MZN</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Informações do Evento --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Informações do Evento</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Evento:</strong> {{ $evento->nome }}
            </div>
            <div class="col-md-6">
                <strong>Local:</strong> {{ $evento->local->nome ?? 'N/A' }}
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>Data Início:</strong> {{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}
            </div>
            <div class="col-md-6">
                <strong>Data Fim:</strong> {{ \Carbon\Carbon::parse($evento->data_fim)->format('d/m/Y') }}
            </div>
        </div>
    </div>
</div>
@endsection