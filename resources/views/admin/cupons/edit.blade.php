{{--
    ============================================================
    VIEW: Editar Cupom
    ============================================================
    Formulário para editar um cupom existente.
--}}

@extends('layouts.admin')

@section('title', 'Editar Cupom - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Cupom: {{ $cupom->codigo }}</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('cupons.update', $cupom->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                           id="codigo" name="codigo" value="{{ old('codigo', $cupom->codigo) }}" required>
                    <small class="text-muted">Código único do cupom</small>
                    @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nome') is-invalid @enderror" 
                           id="nome" name="nome" value="{{ old('nome', $cupom->nome) }}" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="evento_id" class="form-label">Evento</label>
                    <select class="form-select @error('evento_id') is-invalid @enderror" 
                            id="evento_id" name="evento_id">
                        <option value="">Todos os eventos</option>
                        @foreach($eventos as $evento)
                            <option value="{{ $evento->id }}" 
                                    {{ old('evento_id', $cupom->evento_id) == $evento->id ? 'selected' : '' }}>
                                {{ $evento->nome }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Deixe em branco para aplicar a todos os eventos</small>
                    @error('evento_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select class="form-select @error('tipo') is-invalid @enderror" 
                            id="tipo" name="tipo" required>
                        <option value="">Selecione...</option>
                        <option value="percentual" {{ old('tipo', $cupom->tipo) == 'percentual' ? 'selected' : '' }}>Percentual (%)</option>
                        <option value="fixo" {{ old('tipo', $cupom->tipo) == 'fixo' ? 'selected' : '' }}>Fixo (MZN)</option>
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="valor" class="form-label">Valor <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('valor') is-invalid @enderror" 
                           id="valor" name="valor" value="{{ old('valor', $cupom->valor) }}" required>
                    <small class="text-muted">
                        Se for percentual, insira o valor sem o % (ex: 10). 
                        Se for fixo, insira o valor em MZN (ex: 50.00).
                    </small>
                    @error('valor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="valor_minimo" class="form-label">Valor Mínimo</label>
                    <input type="number" step="0.01" class="form-control @error('valor_minimo') is-invalid @enderror" 
                           id="valor_minimo" name="valor_minimo" value="{{ old('valor_minimo', $cupom->valor_minimo) }}">
                    <small class="text-muted">Valor mínimo da compra para aplicar o cupom</small>
                    @error('valor_minimo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="quantidade" class="form-label">Quantidade</label>
                    <input type="number" class="form-control @error('quantidade') is-invalid @enderror" 
                           id="quantidade" name="quantidade" value="{{ old('quantidade', $cupom->quantidade) }}" 
                           placeholder="Deixe em branco para ilimitado">
                    <small class="text-muted">Número máximo de usos</small>
                    @error('quantidade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="validade_inicio" class="form-label">Validade Início</label>
                    <input type="date" class="form-control @error('validade_inicio') is-invalid @enderror" 
                           id="validade_inicio" name="validade_inicio" 
                           value="{{ old('validade_inicio', $cupom->validade_inicio ? \Carbon\Carbon::parse($cupom->validade_inicio)->format('Y-m-d') : '') }}">
                    @error('validade_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="validade_fim" class="form-label">Validade Fim</label>
                    <input type="date" class="form-control @error('validade_fim') is-invalid @enderror" 
                           id="validade_fim" name="validade_fim" 
                           value="{{ old('validade_fim', $cupom->validade_fim ? \Carbon\Carbon::parse($cupom->validade_fim)->format('Y-m-d') : '') }}">
                    @error('validade_fim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input @error('ativo') is-invalid @enderror" 
                           type="checkbox" id="ativo" name="ativo" value="1" 
                           {{ old('ativo', $cupom->ativo) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ativo">
                        Ativo
                    </label>
                    @error('ativo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check"></i> Atualizar
                </button>
                <a href="{{ route('cupons.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection