{{--
    ============================================================
    VIEW: Editar Usuário
    ============================================================
    Formulário para editar um usuário existente.
    
    🔥 Multi-Organizador:
    - Campo para vincular usuário a uma organização
    - Campo para marcar como organizador
--}}

@extends('layouts.admin')

@section('title', 'Editar Usuário - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil-square text-warning"></i> Editar Usuário</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Editando: {{ $user->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Nome --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Telefone --}}
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" name="telefone" id="telefone" 
                               class="form-control @error('telefone') is-invalid @enderror" 
                               value="{{ old('telefone', $user->telefone ?? '') }}" 
                               placeholder="84 123 4567">
                        @error('telefone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NUIT --}}
                    <div class="mb-3">
                        <label for="nuit" class="form-label">NUIT (Moçambique)</label>
                        <input type="text" name="nuit" id="nuit" 
                               class="form-control @error('nuit') is-invalid @enderror" 
                               value="{{ old('nuit', $user->nuit ?? '') }}" 
                               placeholder="123456789">
                        @error('nuit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Papel/Role --}}
                    <div class="mb-3">
                        <label for="role" class="form-label">Papel/Função <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            @foreach($roles as $key => $value)
                                <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <strong>Administrador:</strong> Acesso total ao sistema<br>
                            <strong>Operador:</strong> Acesso ao painel administrativo<br>
                            <strong>Promoter:</strong> Acesso limitado para vendas externas<br>
                            <strong>Cliente:</strong> Acesso apenas ao app mobile
                        </small>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ============================================================
                    🔥 MULTI-ORGANIZADOR: Campos de Organização
                    ============================================================ --}}
                    
                    {{-- Organização --}}
                    <div class="mb-3">
                        <label for="organizacao_id" class="form-label">Organização</label>
                        <select name="organizacao_id" id="organizacao_id" class="form-select">
                            <option value="">Nenhuma</option>
                            @foreach($organizacoes as $org)
                                <option value="{{ $org->id }}" 
                                        {{ old('organizacao_id', $user->organizacao_id) == $org->id ? 'selected' : '' }}>
                                    {{ $org->nome }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Vincule este usuário a uma organização</small>
                    </div>

                    {{-- É Organizador? --}}
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_organizador" id="is_organizador" 
                                   class="form-check-input" value="1"
                                   {{ old('is_organizador', $user->is_organizador) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_organizador">
                                <i class="bi bi-building"></i> É organizador?
                            </label>
                            <small class="text-muted d-block">
                                Organizadores podem criar e gerenciar eventos da organização
                            </small>
                        </div>
                    </div>

                    {{-- Senha --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Nova Senha</label>
                        <input type="password" name="password" id="password" 
                               class="form-control @error('password') is-invalid @enderror">
                        <small class="text-muted">Deixe em branco para manter a senha atual. Mínimo 8 caracteres.</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirmar Senha --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="form-control">
                    </div>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection