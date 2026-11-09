{{--
    ============================================================
    VIEW: Editar Template SMS - SHOTGUN-MOZ
    ============================================================
    Igual ao create, mas pré-preenchido com os dados do template.

    Campos:
    - nome, assunto, tipo, mensagem, is_active
    - Preview em tempo real
    - Botões para inserir variáveis
--}}

@extends('layouts.admin')

@section('title', 'Editar Template de SMS - Shotgun MOZ')

@section('content')

    {{-- ============================================================
         CABEÇALHO
         ============================================================ --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-pencil text-primary"></i> Editar Template
        </h1>
        <a href="{{ route('sms.templates.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="row">

        {{-- ============================================================
             COLUNA ESQUERDA: FORMULÁRIO
             ============================================================ --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Dados do Template</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('sms.templates.update', $template->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="nome" class="form-label">
                                Nome do Template <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nome" id="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome', $template->nome) }}"
                                   maxlength="100"
                                   required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Assunto --}}
                        <div class="mb-3">
                            <label for="assunto" class="form-label">Assunto (opcional)</label>
                            <input type="text" name="assunto" id="assunto"
                                   class="form-control @error('assunto') is-invalid @enderror"
                                   value="{{ old('assunto', $template->assunto) }}"
                                   maxlength="100">
                            <small class="text-muted">Um resumo curto para uso interno.</small>
                            @error('assunto')
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
                                @foreach($tipos as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('tipo', $template->tipo) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mensagem --}}
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">
                                Mensagem <span class="text-danger">*</span>
                            </label>
                            <textarea name="mensagem" id="mensagem"
                                      class="form-control @error('mensagem') is-invalid @enderror"
                                      rows="5" maxlength="480"
                                      required>{{ old('mensagem', $template->mensagem) }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">
                                    Use <code>{variavel}</code> para inserir dados dinâmicos.
                                </small>
                                <small class="text-muted">
                                    <span id="caracteres">0</span> / 480
                                    (<span id="sms_count">0</span> SMS)
                                </small>
                            </div>
                            @error('mensagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Variáveis sugeridas --}}
                        <div class="mb-3">
                            <label class="form-label">Variáveis sugeridas (clique para inserir)</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(['nome', 'evento', 'valor', 'quantidade', 'data', 'local', 'codigo'] as $var)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            onclick="inserirVariavel('{{ $var }}')">
                                        {{ '{' . $var . '}' }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Estado ativo --}}
                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" name="is_active" id="is_active"
                                   class="form-check-input" value="1"
                                   {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Template ativo</label>
                        </div>

                        {{-- Botões --}}
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('sms.templates.index') }}" class="btn btn-secondary me-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> Guardar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ============================================================
             COLUNA DIREITA: PREVIEW
             ============================================================ --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-phone"></i> Pré-visualização</h5>
                </div>
                <div class="card-body">
                    <div style="background: #e5ddd5; padding: 20px; border-radius: 12px; min-height: 200px;">
                        <div style="background: #fff; padding: 12px 16px; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            <div id="preview-conteudo" class="text-break"
                                 style="white-space: pre-wrap; word-wrap: break-word;">
                            </div>
                            <div class="text-end mt-2">
                                <small class="text-muted" id="preview-length">0 / 160</small>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i>
                        As variáveis entre <code>{}</code> aparecem como placeholders no preview.
                    </small>
                </div>
            </div>

            {{-- Dicas --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Dicas</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3 small text-muted">
                        <li>Um SMS tem no máximo <strong>160 caracteres</strong>.</li>
                        <li>Usa <code>{variavel}</code> para personalizar cada envio.</li>
                        <li>Mantém mensagens curtas para reduzir custos.</li>
                        <li>Testa sempre o template antes de o ativar.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

@endsection

{{-- ============================================================
     SCRIPTS
     ============================================================ --}}
@push('scripts')
<script>
    // Referências aos elementos do DOM
    const textarea = document.getElementById('mensagem');
    const preview  = document.getElementById('preview-conteudo');
    const charEl   = document.getElementById('caracteres');
    const countEl  = document.getElementById('sms_count');
    const prevLen  = document.getElementById('preview-length');

    /**
     * Atualiza o preview em tempo real
     */
    function atualizarPreview() {
        preview.textContent = textarea.value || '(vazio)';

        const len = textarea.value.length;
        charEl.textContent = len;
        countEl.textContent = Math.ceil(len / 160);
        prevLen.textContent = len + ' / 160';
    }

    // Escuta mudanças na textarea
    textarea.addEventListener('input', atualizarPreview);
    atualizarPreview();

    /**
     * Insere uma variável na posição do cursor
     *
     * @param {string} variavel - Nome da variável (sem chaves)
     */
    function inserirVariavel(variavel) {
        const start = textarea.selectionStart;
        const end   = textarea.selectionEnd;
        const texto = textarea.value;
        const insercao = '{' + variavel + '}';

        textarea.value = texto.substring(0, start) + insercao + texto.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + insercao.length;
        textarea.focus();
        atualizarPreview();
    }
</script>
@endpush