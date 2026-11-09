{{--
    ============================================================
    VIEW: Editar Template WhatsApp - SHOTGUN-MOZ
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Editar Template WhatsApp - Shotgun MOZ')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-pencil-square text-warning"></i> Editar Template</h1>
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
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Editando: {{ $template->nome }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('whatsapp.templates.update', $template->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" id="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome', $template->nome) }}" required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Código --}}
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" id="codigo"
                                   class="form-control @error('codigo') is-invalid @enderror"
                                   value="{{ old('codigo', $template->codigo) }}"
                                   pattern="[A-Z0-9_]+"
                                   style="text-transform:uppercase"
                                   required>
                            <small class="text-muted">Só letras MAIÚSCULAS, números e underscore (_).</small>
                            @error('codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Categoria --}}
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                            <select name="categoria" id="categoria"
                                    class="form-select @error('categoria') is-invalid @enderror" required>
                                <option value="utilidade"   {{ old('categoria', $template->categoria) == 'utilidade'   ? 'selected' : '' }}>Utilidade</option>
                                <option value="marketing"   {{ old('categoria', $template->categoria) == 'marketing'   ? 'selected' : '' }}>Marketing</option>
                                <option value="autenticacao" {{ old('categoria', $template->categoria) == 'autenticacao' ? 'selected' : '' }}>Autenticação</option>
                            </select>
                            @error('categoria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mensagem --}}
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">Mensagem <span class="text-danger">*</span></label>
                            <textarea name="mensagem" id="mensagem"
                                      class="form-control @error('mensagem') is-invalid @enderror"
                                      rows="6" maxlength="4096"
                                      required>{{ old('mensagem', $template->mensagem) }}</textarea>
                            <small class="text-muted">
                                <span id="caracteres">0</span> caracteres.
                                <br>
                                Variáveis detetadas: <span id="lista-variaveis" class="text-primary"></span>
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
                                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Template ativo</label>
                            </div>
                        </div>

                        {{-- Botões --}}
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-secondary me-2">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mensagem = document.getElementById('mensagem');
        const caracteres = document.getElementById('caracteres');
        const listaVariaveis = document.getElementById('lista-variaveis');

        function extrairVariaveis(txt) {
            const vars = [];
            // {{variavel}}
            const regexDuplas = /\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g;
            let m;
            while ((m = regexDuplas.exec(txt)) !== null) {
                if (!vars.includes(m[1])) vars.push(m[1]);
            }
            // {variavel} (excluindo as já processadas)
            const semDuplas = txt.replace(regexDuplas, '');
            const regexSimples = /\{\s*([a-zA-Z0-9_]+)\s*\}/g;
            while ((m = regexSimples.exec(semDuplas)) !== null) {
                if (!vars.includes(m[1])) vars.push(m[1]);
            }
            return vars;
        }

        function atualizar() {
            caracteres.textContent = mensagem.value.length;
            const vars = extrairVariaveis(mensagem.value);
            listaVariaveis.innerHTML = vars.length > 0
                ? vars.map(v => `<code>{{${v}}}</code>`).join(' ')
                : '<em class="text-muted">nenhuma</em>';
        }

        mensagem.addEventListener('input', atualizar);
        atualizar();
    });
</script>
@endpush