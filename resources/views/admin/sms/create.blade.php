{{--
    ============================================================
    VIEW: Enviar SMS
    ============================================================
    Formulário para enviar um novo SMS.
--}}

@extends('layouts.admin')

@section('title', 'Enviar SMS - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-send text-primary"></i> Enviar SMS</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('sms.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Nova Mensagem</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sms.store') }}" method="POST">
                    @csrf

                    {{-- Telefone --}}
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                        <input type="text" name="telefone" id="telefone" 
                               class="form-control @error('telefone') is-invalid @enderror" 
                               value="{{ old('telefone') }}" 
                               placeholder="84 123 4567" required>
                        <small class="text-muted">Formato: 84 123 4567 (sem +258)</small>
                        @error('telefone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mensagem --}}
                    <div class="mb-3">
                        <label for="mensagem" class="form-label">Mensagem <span class="text-danger">*</span></label>
                        <textarea name="mensagem" id="mensagem" 
                                  class="form-control @error('mensagem') is-invalid @enderror" 
                                  rows="4" maxlength="160" required>{{ old('mensagem') }}</textarea>
                        <small class="text-muted">
                            <span id="caracteres">0</span> / 160 caracteres
                            <br>
                            <span id="sms_count">0</span> SMS
                        </small>
                        @error('mensagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Evento (opcional) --}}
                    <div class="mb-3">
                        <label for="evento_id" class="form-label">Evento (opcional)</label>
                        <select name="evento_id" id="evento_id" class="form-select">
                            <option value="">Nenhum</option>
                            @foreach($eventos ?? [] as $evento)
                                <option value="{{ $evento->id }}" {{ old('evento_id') == $evento->id ? 'selected' : '' }}>
                                    {{ $evento->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Enviar SMS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mensagem = document.getElementById('mensagem');
        const caracteres = document.getElementById('caracteres');
        const smsCount = document.getElementById('sms_count');

        mensagem.addEventListener('input', function() {
            const length = this.value.length;
            caracteres.textContent = length;
            smsCount.textContent = Math.ceil(length / 160);
        });
    });
</script>
@endpush