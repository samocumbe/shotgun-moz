{{--
    ============================================================
    VIEW: Criar Template SMS - SHOTGUN-MOZ
    ============================================================
    Formulário de criação com preview em tempo real.

    Campos:
    - nome       (obrigatório)
    - assunto    (opcional)
    - tipo       → canal de envio: sms | whatsapp
    - categoria  → tipo de negócio: venda | lembrete | promocao | evento | outro
    - mensagem   (obrigatório, máx 480)
    - is_active  (checkbox)

    Funcionalidades:
    - Preview em tempo real estilo SMS
    - Botões para inserir variáveis no cursor
    - Contador de caracteres e SMS
--}}

@extends('layouts.admin')

@section('title', 'Novo Template de SMS - Shotgun MOZ')

@section('content')

    {{-- ============================================================
         CABEÇALHO
         ============================================================ --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-plus-circle text-primary"></i> Novo Template de SMS
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
                    <form action="{{ route('sms.templates.store') }}" method="POST">
                        @csrf

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="nome" class="form-label">
                                Nome do Template <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nome" id="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome') }}"
                                   placeholder="Ex: Confirmação de Compra"
                                   maxlength="100"
                                   required>
                            <small class="text-muted">Nome interno para identificar o template.</small>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Assunto --}}
                        <div class="mb-3">
                            <label for="assunto" class="form-label">Assunto (opcional)</label>
                            <input type="text" name="assunto" id="assunto"
                                   class="form-control @error('assunto') is-invalid @enderror"
                                   value="{{ old('assunto') }}"
                                   placeholder="Ex: CCTDSME"
                                   maxlength="100">
                            <small class="text-muted">Um resumo curto para uso interno.</small>
                            @error('assunto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Linha: Tipo (canal) + Categoria --}}
                        <div class="row">

                            {{-- Canal de envio (tipo) --}}
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">
                                    Canal <span class="text-danger">*</span>
                                </label>
                                <select name="tipo" id="tipo"
                                        class="form-select @error('tipo') is-invalid @enderror"
                                        required>
                                    @foreach(\App\Models\SmsTemplate::TIPOS as $key => $label)
                                        <option value="{{ $key }}" {{ old('tipo') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Por onde o template será enviado.</small>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Categoria de negócio --}}
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">
                                    Categoria <span class="text-danger">*</span>
                                </label>
                                <select name="categoria" id="categoria"
                                        class="form-select @error('categoria') is-invalid @enderror"
                                        required>
                                    @foreach(\App\Models\SmsTemplate::CATEGORIAS as $key => $label)
                                        <option value="{{ $key }}" {{ old('categoria') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Que tipo de mensagem é.</small>
                                @error('categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        {{-- Mensagem --}}
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">
                                Mensagem <span class="text-danger">*</span>
                            </label>

                            {{-- @{{ }} faz o Blade ESCAPAR as chavetas,
                                 permitindo mostrá-las como texto no placeholder. --}}
                            <textarea name="mensagem" id="mensagem"
                                      class="form-control @error('mensagem') is-invalid @enderror"
                                      rows="5" maxlength="480"
                                      placeholder="Ex: Olá @{{nome}}, a sua compra de @{{quantidade}} ingresso(s) para @{{evento}} foi confirmada. Valor: @{{valor}} MT."
                                      required>{{ old('mensagem') }}</textarea>

                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">
                                    Use <code>@{{variavel}}</code> para inserir dados dinâmicos.
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
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Template ativo</label>
                        </div>

                        {{-- Botões --}}
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('sms.templates.index') }}" class="btn btn-secondary me-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> Criar Template
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
                        <div style="background: #fff; padding: 12px 16px; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); max-width: 100%;">
                            <div id="preview-conteudo" class="text-break"
                                 style="white-space: pre-wrap; word-wrap: break-word;">
                                A pré-visualização aparecerá aqui...
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
{{-- @verbatim isola o JS do Blade (para ele não processar { } do JS) --}}
@verbatim
<script>
    // Referências aos elementos do DOM
    var textarea = document.getElementById('mensagem');
    var preview  = document.getElementById('preview-conteudo');
    var charEl   = document.getElementById('caracteres');
    var countEl  = document.getElementById('sms_count');
    var prevLen  = document.getElementById('preview-length');

    /**
     * Atualiza o preview em tempo real
     */
    function atualizarPreview() {
        var texto = textarea.value || 'A pré-visualização aparecerá aqui...';
        preview.textContent = texto;

        var len = textarea.value.length;
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
        var start = textarea.selectionStart;
        var end   = textarea.selectionEnd;
        var texto = textarea.value;
        var insercao = '{' + variavel + '}';

        textarea.value = texto.substring(0, start) + insercao + texto.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + insercao.length;
        textarea.focus();
        atualizarPreview();
    }
</script>
@endverbatim
@endpush