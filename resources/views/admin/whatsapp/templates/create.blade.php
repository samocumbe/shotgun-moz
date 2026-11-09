{{--
    ============================================================
    VIEW: Criar Template WhatsApp - SHOTGUN-MOZ
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Novo Template WhatsApp - Shotgun MOZ')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-plus-circle text-success"></i> Novo Template WhatsApp</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Dados do Template</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('whatsapp.templates.store') }}" method="POST" id="formTemplate">
                        @csrf

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" id="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome') }}"
                                   placeholder="Ex: Confirmação de Compra"
                                   required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Código --}}
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" id="codigo"
                                   class="form-control @error('codigo') is-invalid @enderror"
                                   value="{{ old('codigo') }}"
                                   placeholder="Ex: CONFIRMACAO_COMPRA"
                                   pattern="[A-Z0-9_]+"
                                   style="text-transform:uppercase"
                                   required>
                            <small class="text-muted">
                                Identificador único. Só letras MAIÚSCULAS, números e underscore (_).
                            </small>
                            @error('codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Categoria --}}
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                            <select name="categoria" id="categoria"
                                    class="form-select @error('categoria') is-invalid @enderror" required>
                                <option value="utilidade"    {{ old('categoria') == 'utilidade'    ? 'selected' : '' }}>Utilidade (confirmações, lembretes)</option>
                                <option value="marketing"    {{ old('categoria') == 'marketing'    ? 'selected' : '' }}>Marketing (promoções, novidades)</option>
                                <option value="autenticacao" {{ old('categoria') == 'autenticacao' ? 'selected' : '' }}>Autenticação (códigos de verificação)</option>
                            </select>
                            @error('categoria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mensagem --}}
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">Mensagem <span class="text-danger">*</span></label>

                            {{-- @{{ }} faz o Blade ESCAPAR as chavetas duplas,
                                 deixando-as passar como texto literal no HTML.
                                 Assim o utilizador vê literalmente {{nome}} no campo. --}}
                            <textarea name="mensagem" id="mensagem"
                                      class="form-control @error('mensagem') is-invalid @enderror"
                                      rows="6" maxlength="4096"
                                      placeholder="Olá @{{nome}}, a sua compra de @{{quantidade}} ingressos para o evento @{{evento}} foi confirmada. Total: @{{total}} MZN."
                                      required>{{ old('mensagem') }}</textarea>

                            <small class="text-muted">
                                <span id="caracteres">0</span> caracteres.
                                <br>
                                Use <code>@{{nome}}</code> para inserir variáveis dinâmicas.
                            </small>
                            @error('mensagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ativo --}}
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Ativar este template imediatamente
                                </label>
                            </div>
                        </div>

                        {{-- Botões --}}
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-secondary me-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Criar Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
{{-- @verbatim isola o JS do Blade para não interpretar ${...} ou {{...}} --}}
@verbatim
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var mensagem = document.getElementById('mensagem');
        var caracteres = document.getElementById('caracteres');

        function atualizarContagem() {
            caracteres.textContent = mensagem.value.length;
        }

        mensagem.addEventListener('input', atualizarContagem);
        atualizarContagem();
    });
</script>
@endverbatim
@endpush