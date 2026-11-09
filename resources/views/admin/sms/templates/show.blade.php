{{--
    ============================================================
    VIEW: Detalhes do Template SMS - SHOTGUN-MOZ
    ============================================================
    Exibe o template, variáveis detetadas e preview interativo
    onde o utilizador pode preencher valores para simular o envio.

    Estrutura da tabela sms_templates:
    - id, uuid, organizacao_id, nome, assunto, mensagem, tipo,
      variaveis, is_active, created_at, updated_at
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Template - Shotgun MOZ')

@section('content')

    {{-- ============================================================
         CABEÇALHO
         ============================================================ --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-file-earmark-text text-primary"></i>
            {{ $template->nome }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('sms.templates.edit', $template->id) }}" class="btn btn-sm btn-primary me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('sms.templates.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row">

        {{-- ============================================================
             COLUNA ESQUERDA: INFO + CONTEÚDO
             ============================================================ --}}
        <div class="col-md-7">

            {{-- Card: Informações --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
                </div>
                <div class="card-body">

                    {{-- Linha 1: Tipo + Estado --}}
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Tipo:</strong>
                            <span class="badge bg-{{ $template->tipo_color }}">
                                {{ $template->tipo_label }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Estado:</strong>
                            <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                {{ $template->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>

                    {{-- Linha 2: Assunto --}}
                    @if($template->assunto)
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Assunto:</strong>
                                {{ $template->assunto }}
                            </div>
                        </div>
                    @endif

                    {{-- Linha 3: Datas --}}
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Criado em:</strong>
                            {{ $template->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Atualizado em:</strong>
                            {{ $template->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    {{-- Organização --}}
                    @if($template->organizacao)
                        <div class="row">
                            <div class="col-12 mb-2">
                                <strong>Organização:</strong>
                                {{ $template->organizacao->nome }}
                            </div>
                        </div>
                    @endif

                    {{-- Total de SMS enviados com este template --}}
                    @if($template->sms && $template->sms->count() > 0)
                        <div class="row">
                            <div class="col-12">
                                <strong>SMS enviados com este template:</strong>
                                <span class="badge bg-primary">{{ $template->sms->count() }}</span>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Card: Conteúdo --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat"></i> Conteúdo</h5>
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            onclick="copiarConteudo()"
                            title="Copiar mensagem">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </div>
                <div class="card-body">

                    {{-- Bloco da mensagem --}}
                    <div id="conteudo-template"
                         style="background: #f8f9fa; padding: 15px; border-radius: 8px; white-space: pre-wrap; word-wrap: break-word;">
                        {{ $template->mensagem }}
                    </div>

                    {{-- Contagem de caracteres/SMS --}}
                    <div class="mt-3">
                        @php
                            $len = strlen($template->mensagem);
                            $smsCount = ceil($len / 160);
                        @endphp
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            {{ $len }} caracteres —
                            {{ $smsCount }} {{ $smsCount == 1 ? 'SMS' : 'SMS' }}
                        </small>
                    </div>

                </div>
            </div>

        </div>

        {{-- ============================================================
             COLUNA DIREITA: VARIÁVEIS + PREVIEW
             ============================================================ --}}
        <div class="col-md-5">

            {{-- Card: Variáveis --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-braces"></i> Variáveis Detectadas</h5>
                </div>
                <div class="card-body">
                    @if(count($variaveis) > 0)
                        <p class="text-muted small">
                            Substitua os valores abaixo para simular o envio:
                        </p>

                        @foreach($variaveis as $var)
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text" style="min-width: 110px;">
                                    {{ '{' . $var . '}' }}
                                </span>
                                <input type="text"
                                       class="form-control variavel-input"
                                       data-var="{{ $var }}"
                                       placeholder="Valor para {{ $var }}">
                            </div>
                        @endforeach

                        <button type="button"
                                class="btn btn-sm btn-primary mt-2 w-100"
                                onclick="atualizarPreview()">
                            <i class="bi bi-arrow-clockwise"></i> Atualizar Preview
                        </button>
                    @else
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle"></i>
                            Este template não tem variáveis dinâmicas.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Card: Preview --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-phone"></i> Pré-visualização</h5>
                </div>
                <div class="card-body">
                    <div style="background: #e5ddd5; padding: 20px; border-radius: 12px; min-height: 180px;">
                        <div style="background: #fff; padding: 12px 16px; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            <div id="preview-show" class="text-break"
                                 style="white-space: pre-wrap; word-wrap: break-word;">
                            </div>
                        </div>
                    </div>
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
    // Conteúdo original do template (passado do PHP para JS)
    const conteudoOriginal = @json($template->mensagem);

    /**
     * Renderiza o preview substituindo as variáveis pelos valores
     * introduzidos nos inputs
     */
    function atualizarPreview() {
        let texto = conteudoOriginal;

        document.querySelectorAll('.variavel-input').forEach(input => {
            const varNome = input.dataset.var;
            const valor   = input.value || '{' + varNome + '}';
            // Substitui todas as ocorrências de {varNome} pelo valor
            texto = texto.split('{' + varNome + '}').join(valor);
        });

        document.getElementById('preview-show').textContent = texto;
    }

    /**
     * Copia o conteúdo do template para a área de transferência
     */
    function copiarConteudo() {
        const texto = document.getElementById('conteudo-template').innerText;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(texto)
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copiado!',
                        text: 'Mensagem copiada para a área de transferência.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                })
                .catch(() => fallbackCopiar(texto));
        } else {
            fallbackCopiar(texto);
        }
    }

    /**
     * Fallback para navegadores antigos (sem Clipboard API)
     *
     * @param {string} texto
     */
    function fallbackCopiar(texto) {
        const textarea = document.createElement('textarea');
        textarea.value = texto;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
            Swal.fire({
                icon: 'success',
                title: 'Copiado!',
                timer: 1500,
                showConfirmButton: false
            });
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Não foi possível copiar.'
            });
        }

        document.body.removeChild(textarea);
    }

    // Preview inicial assim que a página carrega
    document.addEventListener('DOMContentLoaded', atualizarPreview);
</script>
@endpush