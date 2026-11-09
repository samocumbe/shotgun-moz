{{--
    ============================================================
    VIEW: Dispositivos - Editar
    ============================================================
    Ficheiro: resources/views/admin/dispositivos/edit.blade.php
--}}

@extends('layouts.admin')

@section('title', 'Editar Dispositivo - Shotgun MOZ')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-pencil-square text-primary"></i>
            Editar Dispositivo
            <small class="text-muted fs-6">— {{ $dispositivo->nome }}</small>
        </h1>
        <a href="{{ route('dispositivos.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong><i class="bi bi-exclamation-triangle"></i> Erros de validação:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dispositivos.update', $dispositivo->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
                    </div>
                    <div class="card-body">

                        {{-- UUID (só leitura) --}}
                        <div class="mb-3">
                            <label class="form-label">UUID</label>
                            <input type="text" class="form-control"
                                   value="{{ $dispositivo->uuid }}" readonly>
                            <small class="text-muted">Identificador único — não é editável.</small>
                        </div>

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="nome" class="form-label">
                                Nome <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nome" id="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome', $dispositivo->nome) }}"
                                   required maxlength="255">
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tipo --}}
                        <div class="mb-3">
                            <label for="tipo" class="form-label">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select name="tipo" id="tipo"
                                    class="form-select @error('tipo') is-invalid @enderror"
                                    required>
                                <option value="scanner"    {{ old('tipo', $dispositivo->tipo) == 'scanner'    ? 'selected' : '' }}>Scanner</option>
                                <option value="pos"        {{ old('tipo', $dispositivo->tipo) == 'pos'        ? 'selected' : '' }}>POS</option>
                                <option value="supervisor" {{ old('tipo', $dispositivo->tipo) == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                                <option value="admin"      {{ old('tipo', $dispositivo->tipo) == 'admin'      ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Evento --}}
                        <div class="mb-3">
                            <label for="evento_id" class="form-label">Evento</label>
                            <select name="evento_id" id="evento_id" class="form-select">
                                <option value="">— Sem evento específico —</option>
                                @foreach($eventos as $ev)
                                    <option value="{{ $ev->id }}"
                                        {{ old('evento_id', $dispositivo->evento_id) == $ev->id ? 'selected' : '' }}>
                                        {{ $ev->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="mb-0">
                            <label for="status" class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="ativo"   {{ old('status', $dispositivo->status) == 'ativo'   ? 'selected' : '' }}>Ativo</option>
                                <option value="inativo" {{ old('status', $dispositivo->status) == 'inativo' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Alterações
                        </button>
                        <a href="{{ route('dispositivos.show', $dispositivo->id) }}"
                           class="btn btn-info">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                        <a href="{{ route('dispositivos.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection