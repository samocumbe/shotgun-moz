{{--
    ============================================================
    VIEW: Editar Promoter
    ============================================================
    Formulário para editar um promoter existente.
--}}

@extends('layouts.admin')

@section('title', 'Editar Promoter - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Promoter: {{ $promoter->nome }}</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('promoters.update', $promoter->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nome') is-invalid @enderror" 
                           id="nome" name="nome" value="{{ old('nome', $promoter->nome) }}" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $promoter->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('telefone') is-invalid @enderror" 
                           id="telefone" name="telefone" value="{{ old('telefone', $promoter->telefone) }}" required>
                    @error('telefone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nuit" class="form-label">NUIT <span class="text-muted">(opcional)</span></label>
                    <input type="text" class="form-control @error('nuit') is-invalid @enderror" 
                           id="nuit" name="nuit" value="{{ old('nuit', $promoter->nuit) }}" maxlength="9">
                    @error('nuit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="comissao_padrao" class="form-label">Comissão Padrão (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('comissao_padrao') is-invalid @enderror" 
                           id="comissao_padrao" name="comissao_padrao" value="{{ old('comissao_padrao', $promoter->comissao_padrao) }}" 
                           min="0" max="100" required>
                    <small class="text-muted">Percentual padrão de comissão sobre as vendas</small>
                    @error('comissao_padrao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" name="status" required>
                        <option value="ativo" {{ old('status', $promoter->status) == 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="pendente" {{ old('status', $promoter->status) == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="inativo" {{ old('status', $promoter->status) == 'inativo' ? 'selected' : '' }}>Inativo</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            {{-- Eventos --}}
            <div class="mb-3">
                <label class="form-label">Eventos Associados</label>
                <select class="form-select @error('eventos') is-invalid @enderror" 
                        id="eventos" name="eventos[]" multiple style="height: 150px;">
                    @foreach($eventos as $evento)
                        <option value="{{ $evento->id }}" 
                                {{ in_array($evento->id, old('eventos', $eventosSelecionados ?? [])) ? 'selected' : '' }}>
                            {{ $evento->nome }} - {{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Selecione os eventos que este promoter poderá vender. Pressione Ctrl para selecionar múltiplos.</small>
                @error('eventos')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                          id="observacoes" name="observacoes" rows="3">{{ old('observacoes', $promoter->observacoes) }}</textarea>
                @error('observacoes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check"></i> Atualizar
                </button>
                <a href="{{ route('promoters.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection