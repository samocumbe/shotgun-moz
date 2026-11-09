{{--
    ============================================================
    VIEW: Página Principal de Relatórios
    ============================================================
    Exibe os cards de acesso aos diferentes relatórios.
    Cada card redireciona para a página de relatório correspondente.
--}}

@extends('layouts.admin')

@section('title', 'Relatórios - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatórios</h1>
</div>

{{-- 
    Obtém o primeiro evento disponível para usar nos links
    Se não houver evento, usa 0 (que redireciona para seleção)
--}}
@php
    $primeiroEvento = $eventos->first();
    $eventoId = $primeiroEvento ? $primeiroEvento->id : 0;
@endphp

<div class="row g-4">
    
    {{-- ========================================================
    CARD: Relatório de Vendas
    ======================================================== --}}
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="icon text-primary" style="font-size: 3rem;">
                    <i class="bi bi-cart"></i>
                </div>
                <h5 class="card-title mt-3">Vendas</h5>
                <p class="card-text text-muted">Relatório completo de vendas por evento</p>
                <a href="{{ route('relatorios.vendas', ['eventoId' => $eventoId]) }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-text"></i> Gerar
                </a>
            </div>
        </div>
    </div>
    
    {{-- ========================================================
    CARD: Relatório de Check-ins
    ======================================================== --}}
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="icon text-success" style="font-size: 3rem;">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <h5 class="card-title mt-3">Check-ins</h5>
                <p class="card-text text-muted">Relatório de entradas e validações</p>
                <a href="{{ route('relatorios.checkins', ['eventoId' => $eventoId]) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-text"></i> Gerar
                </a>
            </div>
        </div>
    </div>
    
    {{-- ========================================================
    CARD: Relatório Financeiro
    ======================================================== --}}
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="icon text-warning" style="font-size: 3rem;">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <h5 class="card-title mt-3">Financeiro</h5>
                <p class="card-text text-muted">Receitas, despesas e comissões</p>
                <a href="{{ route('relatorios.financeiro', ['eventoId' => $eventoId]) }}" class="btn btn-warning">
                    <i class="bi bi-file-earmark-text"></i> Gerar
                </a>
            </div>
        </div>
    </div>
    
    {{-- ========================================================
    CARD: Relatório de Promoters
    ======================================================== --}}
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="icon text-info" style="font-size: 3rem;">
                    <i class="bi bi-people"></i>
                </div>
                <h5 class="card-title mt-3">Promoters</h5>
                <p class="card-text text-muted">Desempenho e comissões dos promoters</p>
                <a href="{{ route('relatorios.promoters') }}" class="btn btn-info">
                    <i class="bi bi-file-earmark-text"></i> Gerar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection