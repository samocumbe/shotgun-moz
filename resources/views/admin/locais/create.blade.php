@extends('layouts.admin')

@section('title', 'Novo Local - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Local</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('locais.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome *</label>
                    <input type="text" class="form-control @error('nome') is-invalid @enderror" 
                           id="nome" name="nome" value="{{ old('nome') }}" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="endereco" class="form-label">Endereço *</label>
                    <input type="text" class="form-control @error('endereco') is-invalid @enderror" 
                           id="endereco" name="endereco" value="{{ old('endereco') }}" required>
                    @error('endereco')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="cidade" class="form-label">Cidade *</label>
                    <input type="text" class="form-control @error('cidade') is-invalid @enderror" 
                           id="cidade" name="cidade" value="{{ old('cidade') }}" required>
                    @error('cidade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="provincia" class="form-label">Província *</label>
                    <select class="form-select @error('provincia') is-invalid @enderror" 
                            id="provincia" name="provincia" required>
                        <option value="">Selecione...</option>
                        <option value="Maputo" {{ old('provincia') == 'Maputo' ? 'selected' : '' }}>Maputo</option>
                        <option value="Maputo Cidade" {{ old('provincia') == 'Maputo Cidade' ? 'selected' : '' }}>Maputo Cidade</option>
                        <option value="Gaza" {{ old('provincia') == 'Gaza' ? 'selected' : '' }}>Gaza</option>
                        <option value="Inhambane" {{ old('provincia') == 'Inhambane' ? 'selected' : '' }}>Inhambane</option>
                        <option value="Sofala" {{ old('provincia') == 'Sofala' ? 'selected' : '' }}>Sofala</option>
                        <option value="Manica" {{ old('provincia') == 'Manica' ? 'selected' : '' }}>Manica</option>
                        <option value="Tete" {{ old('provincia') == 'Tete' ? 'selected' : '' }}>Tete</option>
                        <option value="Zambézia" {{ old('provincia') == 'Zambézia' ? 'selected' : '' }}>Zambézia</option>
                        <option value="Nampula" {{ old('provincia') == 'Nampula' ? 'selected' : '' }}>Nampula</option>
                        <option value="Cabo Delgado" {{ old('provincia') == 'Cabo Delgado' ? 'selected' : '' }}>Cabo Delgado</option>
                        <option value="Niassa" {{ old('provincia') == 'Niassa' ? 'selected' : '' }}>Niassa</option>
                    </select>
                    @error('provincia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="capacidade" class="form-label">Capacidade *</label>
                    <input type="number" class="form-control @error('capacidade') is-invalid @enderror" 
                           id="capacidade" name="capacidade" value="{{ old('capacidade') }}" required min="1">
                    @error('capacidade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control @error('descricao') is-invalid @enderror" 
                              id="descricao" name="descricao" rows="3">{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check"></i> Salvar
                </button>
                <a href="{{ route('locais.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection