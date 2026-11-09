@extends('layouts.admin')

@section('title', 'Detalhes do Cupom - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Cupom</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('cupons.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('cupons.edit', $cupom->id) }}" class="btn btn-sm btn-warning ms-2">
            <i class="bi bi-pencil"></i> Editar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Código:</strong> <code>{{ $cupom->codigo }}</code>
            </div>
            <div class="col-md-6">
                <strong>Nome:</strong> {{ $cupom->nome }}
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <strong>Evento:</strong> {{ $cupom->evento->nome ?? 'Todos os eventos' }}
            </div>
            <div class="col-md-6">
                <strong>Tipo:</strong>
                @if($cupom->tipo == 'percentual')
                    <span class="badge bg-info">Percentual</span>
                @else
                    <span class="badge bg-warning text-dark">Fixo</span>
                @endif
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <strong>Valor:</strong>
                @if($cupom->tipo == 'percentual')
                    {{ number_format($cupom->valor, 0) }}%
                @else
                    {{ number_format($cupom->valor, 2, ',', '.') }} MZN
                @endif
            </div>
            <div class="col-md-6">
                <strong>Valor Mínimo:</strong> {{ number_format($cupom->valor_minimo, 2, ',', '.') }} MZN
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <strong>Usos:</strong> {{ $cupom->usos }}/{{ $cupom->quantidade ?? '∞' }}
            </div>
            <div class="col-md-6">
                <strong>Status:</strong>
                @if($cupom->ativo)
                    <span class="badge badge-status badge-published">Ativo</span>
                @else
                    <span class="badge badge-status badge-draft">Inativo</span>
                @endif
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <strong>Validade Início:</strong> 
                {{ $cupom->validade_inicio ? \Carbon\Carbon::parse($cupom->validade_inicio)->format('d/m/Y') : 'N/A' }}
            </div>
            <div class="col-md-6">
                <strong>Validade Fim:</strong> 
                {{ $cupom->validade_fim ? \Carbon\Carbon::parse($cupom->validade_fim)->format('d/m/Y') : 'N/A' }}
            </div>
        </div>
    </div>
</div>
@endsection