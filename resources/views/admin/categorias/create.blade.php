@extends('layouts.admin')

@section('title', 'Nova Categoria - ' . $evento->nome)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Nova Categoria de Ingresso</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('categorias.store', $evento->id) }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome da Categoria <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nome') is-invalid @enderror" 
                           id="nome" name="nome" value="{{ old('nome') }}" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="preco" class="form-label">Preço (MZN) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('preco') is-invalid @enderror" 
                           id="preco" name="preco" value="{{ old('preco') }}" required>
                    @error('preco')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="quantidade_total" class="form-label">Quantidade Total <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('quantidade_total') is-invalid @enderror" 
                           id="quantidade_total" name="quantidade_total" value="{{ old('quantidade_total') }}" 
                           min="1" required>
                    @error('quantidade_total')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select @error('tipo') is-invalid @enderror" 
                            id="tipo" name="tipo">
                        <option value="normal" {{ old('tipo') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="vip" {{ old('tipo') == 'vip' ? 'selected' : '' }}>VIP</option>
                        <option value="backstage" {{ old('tipo') == 'backstage' ? 'selected' : '' }}>Backstage</option>
                        <option value="meia" {{ old('tipo') == 'meia' ? 'selected' : '' }}>Meia-Entrada</option>
                        <option value="estudante" {{ old('tipo') == 'estudante' ? 'selected' : '' }}>Estudante</option>
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control @error('descricao') is-invalid @enderror" 
                          id="descricao" name="descricao" rows="3">{{ old('descricao') }}</textarea>
                @error('descricao')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check"></i> Salvar
                </button>
                <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection