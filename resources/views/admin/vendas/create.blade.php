{{--
    ============================================================
    VIEW: Nova Venda
    ============================================================
    Formulário para registrar uma nova venda.
    Com estilos específicos para não cortar conteúdo.
--}}

@extends('layouts.admin')

@section('title', 'Nova Venda - Shotgun MOZ')

@push('styles')
<style>
    /* ============================================================
       ESTILOS ESPECÍFICOS PARA A PÁGINA DE NOVA VENDA
       ============================================================ */
    /* Forçar scroll horizontal */
    .main-content {
        overflow-x: auto !important;
        display: block !important;
        width: 100% !important;
        padding: 20px 30px !important;
    }
    
    /* Garantir que o formulário não seja cortado */
    .card-body {
        overflow-x: auto !important;
        min-width: 300px !important;
    }
    
    .row.g-4 {
        min-width: 100% !important;
        margin: 0 !important;
    }
    
    .col-xl-8, .col-lg-7, .col-md-12 {
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }
    
    /* Botões sempre visíveis */
    .btn-group-responsive {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        margin-top: 20px !important;
        padding-bottom: 20px !important;
    }
    
    .btn-group-responsive .btn {
        flex: 1 1 auto !important;
        min-width: 120px !important;
        padding: 10px 20px !important;
        font-size: 1rem !important;
        border-radius: 8px !important;
    }
    
    /* Campos de formulário */
    .form-control, .form-select {
        font-size: 0.9rem !important;
        max-width: 100% !important;
    }
    
    /* Resumo fixo */
    .resumo-card {
        position: sticky !important;
        top: 80px !important;
        z-index: 10 !important;
    }
    
    /* Evitar que a sidebar "pise" no conteúdo */
    .sidebar-wrapper {
        z-index: 1050 !important;
    }
    
    .top-navbar {
        z-index: 1040 !important;
    }
    
    /* Forçar largura total do conteúdo */
    .container-fluid {
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    @media (max-width: 768px) {
        .btn-group-responsive .btn {
            min-width: 80px !important;
            padding: 8px 16px !important;
            font-size: 0.9rem !important;
        }
        .resumo-card {
            position: relative !important;
            top: 0 !important;
        }
        .main-content {
            padding: 15px !important;
        }
    }
    
    @media (max-width: 576px) {
        .btn-group-responsive {
            flex-direction: column !important;
        }
        .btn-group-responsive .btn {
            width: 100% !important;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Nova Venda</h1>
</div>

{{-- ============================================================
    FORMULÁRIO - Layout com grid
    ============================================================ --}}
<div class="row g-4">
    
    {{-- COLUNA ESQUERDA: Formulário --}}
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('vendas.store') }}" method="POST" id="form-venda">
                    @csrf
                    
                    {{-- Evento e Categoria --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="evento_id" class="form-label fw-bold">
                                Evento <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('evento_id') is-invalid @enderror" 
                                    id="evento_id" name="evento_id" required>
                                <option value="">Selecione um evento...</option>
                                @foreach($eventos as $evento)
                                    <option value="{{ $evento->id }}" 
                                            {{ old('evento_id') == $evento->id ? 'selected' : '' }}>
                                        {{ $evento->nome }} - {{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('evento_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="categoria_ingresso_id" class="form-label fw-bold">
                                Categoria de Ingresso <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('categoria_ingresso_id') is-invalid @enderror" 
                                    id="categoria_ingresso_id" name="categoria_ingresso_id" required>
                                <option value="">Selecione um evento primeiro...</option>
                            </select>
                            @error('categoria_ingresso_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    {{-- Dados do Cliente --}}
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="participante_nome" class="form-label fw-bold">
                                Nome Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('participante_nome') is-invalid @enderror" 
                                   id="participante_nome" name="participante_nome" 
                                   value="{{ old('participante_nome') }}" 
                                   placeholder="Ex: João da Silva" required>
                            @error('participante_nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="participante_email" class="form-label fw-bold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control @error('participante_email') is-invalid @enderror" 
                                   id="participante_email" name="participante_email" 
                                   value="{{ old('participante_email') }}" 
                                   placeholder="Ex: joao@email.com" required>
                            @error('participante_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="participante_telefone" class="form-label fw-bold">
                                Telefone <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('participante_telefone') is-invalid @enderror" 
                                   id="participante_telefone" name="participante_telefone" 
                                   value="{{ old('participante_telefone') }}" 
                                   placeholder="Ex: 821234567" required>
                            @error('participante_telefone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="participante_nuit" class="form-label fw-bold">
                                NUIT <span class="text-muted">(opcional)</span>
                            </label>
                            <input type="text" class="form-control @error('participante_nuit') is-invalid @enderror" 
                                   id="participante_nuit" name="participante_nuit" 
                                   value="{{ old('participante_nuit') }}" 
                                   placeholder="Ex: 123456789" maxlength="9" pattern="[0-9]{9}">
                            <small class="text-muted">Número Único de Identificação Tributária (9 dígitos)</small>
                            @error('participante_nuit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    {{-- Quantidade, Pagamento e Tipo --}}
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label for="quantidade" class="form-label fw-bold">
                                Quantidade <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control @error('quantidade') is-invalid @enderror" 
                                   id="quantidade" name="quantidade" value="{{ old('quantidade', 1) }}" 
                                   min="1" max="10" required>
                            @error('quantidade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="metodo_pagamento" class="form-label fw-bold">
                                Método de Pagamento <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('metodo_pagamento') is-invalid @enderror" 
                                    id="metodo_pagamento" name="metodo_pagamento" required>
                                <option value="">Selecione...</option>
                                <option value="dinheiro" {{ old('metodo_pagamento') == 'dinheiro' ? 'selected' : '' }}>
                                    💰 Dinheiro
                                </option>
                                <option value="mpesa" {{ old('metodo_pagamento') == 'mpesa' ? 'selected' : '' }}>
                                    📱 M-Pesa
                                </option>
                                <option value="emola" {{ old('metodo_pagamento') == 'emola' ? 'selected' : '' }}>
                                    📱 e-Mola
                                </option>
                                <option value="cartao" {{ old('metodo_pagamento') == 'cartao' ? 'selected' : '' }}>
                                    💳 Cartão
                                </option>
                            </select>
                            @error('metodo_pagamento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="tipo_venda" class="form-label fw-bold">
                                Tipo de Venda <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('tipo_venda') is-invalid @enderror" 
                                    id="tipo_venda" name="tipo_venda" required>
                                <option value="">Selecione...</option>
                                <option value="presencial" {{ old('tipo_venda') == 'presencial' ? 'selected' : '' }}>
                                    🏪 Presencial
                                </option>
                                <option value="online" {{ old('tipo_venda') == 'online' ? 'selected' : '' }}>
                                    🌐 Online
                                </option>
                                <option value="promoter" {{ old('tipo_venda') == 'promoter' ? 'selected' : '' }}>
                                    📢 Promoter
                                </option>
                            </select>
                            @error('tipo_venda')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    {{-- Observações --}}
                    <div class="mt-3">
                        <label for="observacoes" class="form-label fw-bold">Observações</label>
                        <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                                  id="observacoes" name="observacoes" rows="2"
                                  placeholder="Informações adicionais sobre a venda...">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- BOTÕES - SEMPRE VISÍVEIS --}}
                    <div class="btn-group-responsive">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check"></i> Finalizar Venda
                        </button>
                        <a href="{{ route('vendas.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- COLUNA DIREITA: Resumo --}}
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card resumo-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Resumo</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Evento</small>
                    <p id="resumo-evento" class="fw-bold text-truncate">
                        <span class="text-secondary">Selecione um evento</span>
                    </p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Categoria</small>
                    <p id="resumo-categoria" class="fw-bold">
                        <span class="text-secondary">Selecione uma categoria</span>
                    </p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Quantidade</small>
                    <p id="resumo-quantidade" class="fw-bold">0</p>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">Total</small>
                    <h3 id="resumo-total" class="text-success">0,00 MZN</h3>
                </div>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <small>Os ingressos serão gerados automaticamente após a confirmação da venda.</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
SCRIPTS
============================================================ --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Script de vendas carregado');
    
    const eventoSelect = document.getElementById('evento_id');
    const categoriaSelect = document.getElementById('categoria_ingresso_id');
    const quantidadeInput = document.getElementById('quantidade');
    
    const resumoEvento = document.getElementById('resumo-evento');
    const resumoCategoria = document.getElementById('resumo-categoria');
    const resumoQuantidade = document.getElementById('resumo-quantidade');
    const resumoTotal = document.getElementById('resumo-total');
    
    function carregarCategorias(eventoId) {
        if (!eventoId) {
            categoriaSelect.innerHTML = '<option value="">Selecione um evento primeiro...</option>';
            return;
        }
        const url = '/admin/vendas/eventos/' + eventoId + '/categorias';
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Erro HTTP: ' + response.status);
                return response.json();
            })
            .then(data => {
                categoriaSelect.innerHTML = '<option value="">Selecione uma categoria...</option>';
                if (!data || data.length === 0) {
                    categoriaSelect.innerHTML = '<option value="">Nenhuma categoria disponível</option>';
                    return;
                }
                data.forEach(categoria => {
                    const option = document.createElement('option');
                    option.value = categoria.id;
                    const preco = parseFloat(categoria.preco) || 0;
                    option.dataset.preco = preco;
                    option.textContent = categoria.nome + 
                        ' - ' + preco.toFixed(2).replace('.', ',') + ' MZN' +
                        ' (' + categoria.disponiveis + ' disponíveis)';
                    categoriaSelect.appendChild(option);
                });
                atualizarResumo();
            })
            .catch(error => {
                console.error('❌ Erro:', error);
                categoriaSelect.innerHTML = '<option value="">Erro ao carregar categorias</option>';
            });
    }
    
    function atualizarResumo() {
        const eventoOption = eventoSelect.options[eventoSelect.selectedIndex];
        const eventoNome = eventoOption && eventoOption.value 
            ? eventoOption.text.split(' - ')[0] 
            : 'Selecione um evento';
        
        const categoriaOption = categoriaSelect.options[categoriaSelect.selectedIndex];
        const categoriaNome = categoriaOption && categoriaOption.value 
            ? categoriaOption.text 
            : 'Selecione uma categoria';
        const preco = parseFloat(categoriaOption?.dataset?.preco) || 0;
        
        const quantidade = parseInt(quantidadeInput.value) || 0;
        const total = preco * quantidade;
        
        resumoEvento.textContent = eventoNome;
        resumoCategoria.textContent = categoriaNome;
        resumoQuantidade.textContent = quantidade;
        resumoTotal.textContent = total.toFixed(2).replace('.', ',') + ' MZN';
        resumoTotal.className = total === 0 ? 'text-muted' : 'text-success';
    }
    
    eventoSelect.addEventListener('change', function() {
        carregarCategorias(this.value);
        atualizarResumo();
    });
    categoriaSelect.addEventListener('change', atualizarResumo);
    quantidadeInput.addEventListener('input', atualizarResumo);
    
    if (eventoSelect.value) {
        carregarCategorias(eventoSelect.value);
    }
    atualizarResumo();
});
</script>
@endpush
@endsection