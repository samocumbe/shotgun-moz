{{--
    ============================================================
    VIEW: Relatório de Promoters
    ============================================================
    Exibe o relatório de desempenho de todos os promoters.
    
    Funcionalidades:
    1. Lista todos os promoters com suas estatísticas
    2. Filtros por status e busca por nome/email
    3. Botão para exportar relatório completo em PDF
    4. Botão para ver relatório detalhado de cada promoter
    5. Botão para exportar PDF detalhado de cada promoter
    6. Cards com resumo geral (total de promoters, ativos, vendas, comissões)
--}}

@extends('layouts.admin')

@section('title', 'Relatório de Promoters - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório de Promoters</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('relatorios.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        {{-- 
            Botão para exportar relatório completo de todos os promoters em PDF
            A URL inclui ?exportar=pdf para indicar que é uma exportação
        --}}
        <a href="{{ route('relatorios.promoters') }}?exportar=pdf" class="btn btn-sm btn-danger ms-2">
            <i class="bi bi-file-pdf"></i> PDF Completo
        </a>
    </div>
</div>

{{-- ============================================================
    CARDS DE RESUMO GERAL
    ============================================================ --}}
<div class="row mb-4">
    {{-- Total de Promoters --}}
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Total Promoters</small>
            <h4>{{ $totais['total_promoters'] }}</h4>
        </div>
    </div>
    
    {{-- Promoters Ativos --}}
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Ativos</small>
            <h4 class="text-success">{{ $totais['ativos'] }}</h4>
        </div>
    </div>
    
    {{-- Total de Vendas Geradas --}}
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Total Vendas</small>
            <h4>{{ number_format($totais['total_vendas'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
    
    {{-- Total de Comissões Geradas --}}
    <div class="col-md-3">
        <div class="stat-card">
            <small class="text-muted">Total Comissões</small>
            <h4 class="text-warning">{{ number_format($totais['total_comissoes'], 2, ',', '.') }} MZN</h4>
        </div>
    </div>
</div>

{{-- ============================================================
    FILTROS
    ============================================================ --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('relatorios.promoters') }}" method="GET" class="row g-3">
            {{-- Busca por nome ou email --}}
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Nome ou email..." value="{{ request('search') }}">
            </div>
            
            {{-- Filtro por status --}}
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                </select>
            </div>
            
            {{-- Botões de ação --}}
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="{{ route('relatorios.promoters') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
    TABELA DE PROMOTERS
    ============================================================ --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Código</th>
                        <th>Comissão</th>
                        <th>Vendas (MZN)</th>
                        <th>Comissão Recebida (MZN)</th>
                        <th>Status</th>
                        <th style="min-width: 120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($estatisticas as $promoter)
                    <tr>
                        {{-- Nome do Promoter --}}
                        <td>
                            <strong>{{ $promoter['nome'] }}</strong>
                        </td>
                        
                        {{-- Email --}}
                        <td>{{ $promoter['email'] }}</td>
                        
                        {{-- Código Promocional --}}
                        <td>
                            <code>{{ $promoter['codigo'] }}</code>
                        </td>
                        
                        {{-- Percentual de Comissão --}}
                        <td>{{ number_format($promoter['comissao_padrao'], 0) }}%</td>
                        
                        {{-- Total de Vendas --}}
                        <td>
                            <strong>{{ number_format($promoter['total_vendas'], 2, ',', '.') }}</strong>
                        </td>
                        
                        {{-- Comissões (Pagas e Pendentes) --}}
                        <td>
                            {{-- Comissões Pagas (verde) --}}
                            <span class="text-success">
                                <i class="bi bi-check-circle"></i> 
                                {{ number_format($promoter['comissoes_pagas'], 2, ',', '.') }}
                            </span>
                            <br>
                            {{-- Comissões Pendentes (amarelo) --}}
                            <span class="text-warning">
                                <i class="bi bi-clock"></i> 
                                {{ number_format($promoter['comissoes_pendentes'], 2, ',', '.') }}
                            </span>
                        </td>
                        
                        {{-- Status do Promoter --}}
                        <td>
                            @if($promoter['status'] == 'ativo')
                                <span class="badge badge-status badge-published">
                                    <i class="bi bi-check-circle"></i> Ativo
                                </span>
                            @elseif($promoter['status'] == 'inativo')
                                <span class="badge badge-status badge-canceled">
                                    <i class="bi bi-x-circle"></i> Inativo
                                </span>
                            @elseif($promoter['status'] == 'pendente')
                                <span class="badge badge-status badge-draft">
                                    <i class="bi bi-clock"></i> Pendente
                                </span>
                            @endif
                        </td>
                        
                        {{-- Botões de Ação --}}
                        <td>
                            {{-- 
                                Botão "Relatório Detalhado" - abre a página com todos os detalhes 
                                do promoter (vendas por evento, histórico de comissões, etc)
                            --}}
                            <a href="{{ route('relatorios.promoter-detalhes', $promoter['id']) }}" 
                               class="btn btn-sm btn-info" 
                               title="Relatório Detalhado"
                               data-bs-toggle="tooltip">
                                <i class="bi bi-file-text"></i>
                            </a>
                            
                            {{-- 
                                Botão "PDF Detalhado" - exporta o relatório detalhado em PDF
                                Inclui todas as informações do promoter formatadas para impressão
                            --}}
                            <a href="{{ route('relatorios.promoter-detalhes', $promoter['id']) }}?exportar=pdf" 
                               class="btn btn-sm btn-danger" 
                               title="PDF Detalhado"
                               data-bs-toggle="tooltip">
                                <i class="bi bi-file-pdf"></i>
                            </a>
                            
                            {{-- 
                                Botão "Ver Perfil" - redireciona para a página de detalhes do promoter
                                (mantido para compatibilidade com a FASE 4)
                            --}}
                            <a href="{{ route('promoters.show', $promoter['id']) }}" 
                               class="btn btn-sm btn-secondary" 
                               title="Ver Perfil"
                               data-bs-toggle="tooltip">
                                <i class="bi bi-person"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    {{-- Mensagem quando não há promoters cadastrados --}}
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhum promoter encontrado.</p>
                            <a href="{{ route('promoters.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus"></i> Cadastrar Promoter
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                {{-- 
                    Rodapé da tabela com totais gerais
                    Útil para ter uma visão rápida dos valores totais
                --}}
                @if($estatisticas->count() > 0)
                <tfoot>
                    <tr class="table-active fw-bold">
                        <td colspan="4" class="text-end">TOTAIS:</td>
                        <td>{{ number_format($totais['total_vendas'], 2, ',', '.') }} MZN</td>
                        <td>{{ number_format($totais['total_comissoes'], 2, ',', '.') }} MZN</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- ============================================================
    LEGENDA E INFORMAÇÕES ADICIONAIS
    ============================================================ --}}
<div class="row mt-3">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <strong>Legenda:</strong>
            <span class="ms-3 text-success"><i class="bi bi-check-circle"></i> Comissões Pagas</span>
            <span class="ms-3 text-warning"><i class="bi bi-clock"></i> Comissões Pendentes</span>
            <span class="ms-3 text-primary"><i class="bi bi-file-text"></i> Relatório Detalhado</span>
            <span class="ms-3 text-danger"><i class="bi bi-file-pdf"></i> PDF Detalhado</span>
        </div>
    </div>
</div>
@endsection

{{-- ============================================================
    SCRIPTS ADICIONAIS
    ============================================================ --}}
@push('scripts')
<script>
    /**
     * ============================================================
     * INICIALIZAR TOOLTIPS DO BOOTSTRAP
     * ============================================================
     * Ativa os tooltips para todos os elementos com data-bs-toggle="tooltip"
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips do Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush