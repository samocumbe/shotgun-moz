@extends('layouts.admin')

@section('title', 'Editar Local - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Local: {{ $local->nome }}</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('locais.update', $local->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome *</label>
                    <input type="text" class="form-control @error('nome') is-invalid @enderror" 
                           id="nome" name="nome" value="{{ old('nome', $local->nome) }}" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="endereco" class="form-label">Endereço *</label>
                    <input type="text" class="form-control @error('endereco') is-invalid @enderror" 
                           id="endereco" name="endereco" value="{{ old('endereco', $local->endereco) }}" required>
                    @error('endereco')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="cidade" class="form-label">Cidade *</label>
                    <input type="text" class="form-control @error('cidade') is-invalid @enderror" 
                           id="cidade" name="cidade" value="{{ old('cidade', $local->cidade) }}" required>
                    @error('cidade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="provincia" class="form-label">Província *</label>
                    <select class="form-select @error('provincia') is-invalid @enderror" 
                            id="provincia" name="provincia" required>
                        <option value="">Selecione...</option>
                        <option value="Maputo" {{ old('provincia', $local->provincia) == 'Maputo' ? 'selected' : '' }}>Maputo</option>
                        <option value="Maputo Cidade" {{ old('provincia', $local->provincia) == 'Maputo Cidade' ? 'selected' : '' }}>Maputo Cidade</option>
                        <option value="Gaza" {{ old('provincia', $local->provincia) == 'Gaza' ? 'selected' : '' }}>Gaza</option>
                        <option value="Inhambane" {{ old('provincia', $local->provincia) == 'Inhambane' ? 'selected' : '' }}>Inhambane</option>
                        <option value="Sofala" {{ old('provincia', $local->provincia) == 'Sofala' ? 'selected' : '' }}>Sofala</option>
                        <option value="Manica" {{ old('provincia', $local->provincia) == 'Manica' ? 'selected' : '' }}>Manica</option>
                        <option value="Tete" {{ old('provincia', $local->provincia) == 'Tete' ? 'selected' : '' }}>Tete</option>
                        <option value="Zambézia" {{ old('provincia', $local->provincia) == 'Zambézia' ? 'selected' : '' }}>Zambézia</option>
                        <option value="Nampula" {{ old('provincia', $local->provincia) == 'Nampula' ? 'selected' : '' }}>Nampula</option>
                        <option value="Cabo Delgado" {{ old('provincia', $local->provincia) == 'Cabo Delgado' ? 'selected' : '' }}>Cabo Delgado</option>
                        <option value="Niassa" {{ old('provincia', $local->provincia) == 'Niassa' ? 'selected' : '' }}>Niassa</option>
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
                           id="capacidade" name="capacidade" value="{{ old('capacidade', $local->capacidade) }}" required min="1">
                    @error('capacidade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control @error('descricao') is-invalid @enderror" 
                              id="descricao" name="descricao" rows="3">{{ old('descricao', $local->descricao) }}</textarea>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check"></i> Atualizar
                </button>
                <a href="{{ route('locais.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection