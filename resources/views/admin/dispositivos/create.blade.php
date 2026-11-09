{{--
    ============================================================
    VIEW: Dispositivos - Criar
    ============================================================
    Ficheiro: resources/views/admin/dispositivos/create.blade.php
--}}

@extends('layouts.admin')

@section('title', 'Novo Dispositivo - Shotgun MOZ')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-phone text-primary"></i> Novo Dispositivo
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

    <form action="{{ route('dispositivos.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
                    </div>
                    <div class="card-body">

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="nome" class="form-label">
                                Nome do Dispositivo <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nome" id="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome') }}"
                                   placeholder="Ex: Scanner Principal"
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
                                <option value="">— Escolhe um tipo —</option>
                                <option value="scanner"    {{ old('tipo') == 'scanner'    ? 'selected' : '' }}>
                                    Scanner (validação de QR)
                                </option>
                                <option value="pos"        {{ old('tipo') == 'pos'        ? 'selected' : '' }}>
                                    POS (venda de ingressos)
                                </option>
                                <option value="supervisor" {{ old('tipo') == 'supervisor' ? 'selected' : '' }}>
                                    Supervisor (monitorização)
                                </option>
                                <option value="admin"      {{ old('tipo') == 'admin'      ? 'selected' : '' }}>
                                    Admin (acesso total)
                                </option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Evento --}}
                        <div class="mb-3">
                            <label for="evento_id" class="form-label">Evento (opcional)</label>
                            <select name="evento_id" id="evento_id"
                                    class="form-select @error('evento_id') is-invalid @enderror">
                                <option value="">— Sem evento específico —</option>
                                @foreach($eventos as $ev)
                                    <option value="{{ $ev->id }}" {{ old('evento_id') == $ev->id ? 'selected' : '' }}>
                                        {{ $ev->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Se associar a um evento, este dispositivo só pode validar ingressos desse evento.
                            </small>
                            @error('evento_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="mb-0">
                            <label for="status" class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="ativo"   {{ old('status', 'ativo') == 'ativo'   ? 'selected' : '' }}>
                                    Ativo
                                </option>
                                <option value="inativo" {{ old('status') == 'inativo' ? 'selected' : '' }}>
                                    Inativo
                                </option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Coluna direita: ações --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Criar Dispositivo
                        </button>
                        <a href="{{ route('dispositivos.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Um <strong>UUID</strong> único será gerado automaticamente para
                        autenticar este dispositivo na API.
                    </small>
                </div>
            </div>
        </div>
    </form>

@endsection