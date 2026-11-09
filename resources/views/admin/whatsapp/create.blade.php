{{--
    ============================================================
    VIEW: Enviar WhatsApp - SHOTGUN-MOZ
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Enviar WhatsApp - Shotgun MOZ')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-whatsapp text-success"></i> Enviar WhatsApp</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-secondary">
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
                    <form action="{{ route('whatsapp.store') }}" method="POST" id="formWhatsapp">
                        @csrf

                        {{-- Template (opcional) --}}
                        <div class="mb-3">
                            <label for="template_id" class="form-label">Template (opcional)</label>
                            <select name="template_id" id="template_id" class="form-select">
                                <option value="">Nenhum</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}"
                                            data-mensagem="{{ $tpl->mensagem }}"
                                            {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                                        {{ $tpl->nome }} ({{ $tpl->codigo }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Ao escolher um template, a mensagem é preenchida automaticamente.</small>
                        </div>

                        {{-- Telefone --}}
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                            <input type="text" name="telefone" id="telefone"
                                   class="form-control @error('telefone') is-invalid @enderror"
                                   value="{{ old('telefone') }}"
                                   placeholder="84 123 4567" required>
                            <small class="text-muted">Formato: 84 123 4567 (o +258 é adicionado automaticamente)</small>
                            @error('telefone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mensagem --}}
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">Mensagem <span class="text-danger">*</span></label>
                            <textarea name="mensagem" id="mensagem"
                                      class="form-control @error('mensagem') is-invalid @enderror"
                                      rows="6" maxlength="4096" required>{{ old('mensagem') }}</textarea>
                            <small class="text-muted">
                                <span id="caracteres">0</span> caracteres
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
                                    <option value="{{ $evento->id }}"
                                            {{ old('evento_id') == $evento->id ? 'selected' : '' }}>
                                        {{ $evento->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-whatsapp"></i> Enviar WhatsApp
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
        const templateSelect = document.getElementById('template_id');

        mensagem.addEventListener('input', function() {
            caracteres.textContent = this.value.length;
        });

        templateSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const msg = opt.getAttribute('data-mensagem');
            if (msg) {
                mensagem.value = msg;
                caracteres.textContent = msg.length;
            }
        });

        // Contagem inicial
        caracteres.textContent = mensagem.value.length;
    });
</script>
@endpush