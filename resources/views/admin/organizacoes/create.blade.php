{{--
    ============================================================
    VIEW: Criar Organização
    ============================================================
    Formulário para criar uma nova organização.
    Campos:
    - nome: Nome da organização
    - email: Email de contato
    - telefone: Telefone de contato
    - endereco: Endereço
    - website: Site da organização
    - descricao: Descrição
    - status: ativo, inativo, suspenso
--}}

@extends('layouts.admin')

@section('title', 'Nova Organização - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-building-add text-primary"></i> Nova Organização</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('organizacoes.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Dados da Organização</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('organizacoes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Nome --}}
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Organização <span class="text-danger">*</span></label>
                        <input type="text" name="nome" id="nome" 
                               class="form-control @error('nome') is-invalid @enderror" 
                               value="{{ old('nome') }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email e Telefone --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="telefone" 
                                   class="form-control @error('telefone') is-invalid @enderror" 
                                   value="{{ old('telefone') }}" placeholder="84 123 4567">
                            @error('telefone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Website e Status --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" name="website" id="website" 
                                   class="form-control @error('website') is-invalid @enderror" 
                                   value="{{ old('website') }}" placeholder="https://exemplo.com">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="ativo" {{ old('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                <option value="inativo" {{ old('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                                <option value="suspenso" {{ old('status') == 'suspenso' ? 'selected' : '' }}>Suspenso</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Endereço --}}
                    <div class="mb-3">
                        <label for="endereco" class="form-label">Endereço</label>
                        <textarea name="endereco" id="endereco" 
                                  class="form-control @error('endereco') is-invalid @enderror" 
                                  rows="2">{{ old('endereco') }}</textarea>
                        @error('endereco')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Descrição --}}
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea name="descricao" id="descricao" 
                                  class="form-control @error('descricao') is-invalid @enderror" 
                                  rows="3">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Logo --}}
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" name="logo" id="logo" 
                               class="form-control @error('logo') is-invalid @enderror" 
                               accept="image/*">
                        <small class="text-muted">Formato recomendado: PNG ou JPG, 300x300px</small>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('organizacoes.index') }}" class="btn btn-secondary me-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Criar Organização
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection