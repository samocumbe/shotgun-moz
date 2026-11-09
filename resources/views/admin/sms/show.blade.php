{{--
    ============================================================
    VIEW: Detalhes do SMS - SHOTGUN-MOZ
    ============================================================
    Exibe todas as informações de um SMS específico.

    Funcionalidades:
    - Informações completas (ID, UUID, telefone, status, provedor)
    - Datas de criação, envio, entrega
    - Mensagem completa com contagem de caracteres/SMS
    - Link para o evento associado (se existir)
    - Erro detalhado (se houver falha)
    - Ações: reenviar (se falha), imprimir, voltar

    Dependências:
    - Layout: layouts.admin
    - Bootstrap 5, Bootstrap Icons, SweetAlert2
    - Rotas: sms.index, sms.reenviar, eventos.show
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do SMS - Shotgun MOZ')

@section('content')

    {{-- ============================================================
         CABEÇALHO DA PÁGINA
         ============================================================ --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-chat-dots text-primary"></i>
            Detalhes do SMS <span class="text-muted">#{{ $sms->id }}</span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('sms.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row">

        {{-- ============================================================
             COLUNA PRINCIPAL (ESQUERDA)
             ============================================================ --}}
        <div class="col-md-8">

            {{-- ---------- CARD: INFORMAÇÕES ---------- --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
                </div>
                <div class="card-body">

                    {{-- Linha 1: ID + UUID --}}
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>ID:</strong> #{{ $sms->id }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>UUID:</strong>
                            <code class="text-truncate d-inline-block" style="max-width: 100%;">
                                {{ $sms->uuid }}
                            </code>
                        </div>
                    </div>

                    {{-- Linha 2: Telefone + Status --}}
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Telefone:</strong>
                            <code>{{ $sms->telefone }}</code>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Status:</strong>
                            @switch($sms->status)
                                @case('pendente')
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                    @break
                                @case('enviado')
                                    <span class="badge bg-success">Enviado</span>
                                    @break
                                @case('entregue')
                                    <span class="badge bg-info text-dark">Entregue</span>
                                    @break
                                @case('falha')
                                    <span class="badge bg-danger">Falha</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $sms->status }}</span>
                            @endswitch
                        </div>
                    </div>

                    {{-- Linha 3: Provedor + Referência --}}
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Provedor:</strong>
                            {{ $sms->provedor ?? 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Referência:</strong>
                            {{ $sms->referencia ?? 'N/A' }}
                        </div>
                    </div>

                    {{-- Linha 4: Tentativas --}}
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Tentativas:</strong>
                            {{ $sms->tentativas ?? 0 }}
                        </div>
                    </div>

                    <hr>

                    {{-- Linha 5: Datas --}}
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Criado em:</strong>
                            {{ $sms->created_at ? $sms->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Enviado em:</strong>
                            {{ $sms->data_envio ? $sms->data_envio->format('d/m/Y H:i:s') : 'N/A' }}
                        </div>
                    </div>

                    @if($sms->data_entrega)
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Entregue em:</strong>
                                {{ $sms->data_entrega->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    @endif

                    {{-- Erro (se houver) --}}
                    @if($sms->erro)
                        <div class="row mt-3">
                            <div class="col-12">
                                <strong class="text-danger">
                                    <i class="bi bi-exclamation-triangle"></i> Erro:
                                </strong>
                                <div class="alert alert-danger mt-2 mb-0">
                                    {{ $sms->erro }}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Evento associado --}}
                    @if($sms->evento)
                        <div class="row mt-3">
                            <div class="col-12">
                                <strong>Evento:</strong>
                                <a href="{{ route('eventos.show', $sms->evento->id) }}"
                                   class="ms-1">
                                    <i class="bi bi-calendar-event"></i>
                                    {{ $sms->evento->nome }}
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Organização --}}
                    @if($sms->organizacao)
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Organização:</strong>
                                {{ $sms->organizacao->nome }}
                            </div>
                        </div>
                    @endif

                    {{-- Utilizador que enviou --}}
                    @if($sms->user)
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Enviado por:</strong>
                                {{ $sms->user->name }}
                                <small class="text-muted">({{ $sms->user->email }})</small>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- ---------- CARD: MENSAGEM ---------- --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat"></i> Mensagem</h5>
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            onclick="copiarMensagem()"
                            title="Copiar mensagem">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </div>
                <div class="card-body">

                    {{-- Bloco da mensagem (fundo cinza claro) --}}
                    <div id="mensagem-completa"
                         style="background: #f8f9fa; padding: 15px; border-radius: 8px; white-space: pre-wrap; word-wrap: break-word;">
                        {{ $sms->mensagem }}
                    </div>

                    {{-- Contagem de caracteres/SMS --}}
                    <div class="mt-3">
                        @php
                            $caracteres = strlen($sms->mensagem);
                            $smsCount = ceil($caracteres / 160);
                        @endphp
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            {{ $caracteres }} caracteres —
                            {{ $smsCount }} {{ $smsCount == 1 ? 'SMS' : 'SMS' }}
                        </small>
                    </div>

                </div>
            </div>

        </div>

        {{-- ============================================================
             COLUNA LATERAL (DIREITA) — AÇÕES
             ============================================================ --}}
        <div class="col-md-4">

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
                </div>
                <div class="card-body d-grid gap-2">

                    {{-- Reenviar (só se falha) --}}
                    @if($sms->status == 'falha')
                        <button type="button"
                                onclick="reenviarSms({{ $sms->id }})"
                                class="btn btn-warning">
                            <i class="bi bi-arrow-repeat"></i> Reenviar SMS
                        </button>
                    @endif

                    {{-- Imprimir --}}
                    <button type="button"
                            onclick="window.print()"
                            class="btn btn-primary">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>

                    {{-- Copiar telefone --}}
                    <button type="button"
                            onclick="copiarTelefone()"
                            class="btn btn-outline-secondary">
                        <i class="bi bi-clipboard"></i> Copiar Telefone
                    </button>

                    {{-- Voltar --}}
                    <a href="{{ route('sms.index') }}"
                       class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar à lista
                    </a>

                </div>
            </div>

            {{-- Card informativo extra --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Dica</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0 small">
                        Podes usar o botão <strong>Imprimir</strong> para gerar
                        uma versão em PDF do SMS (via "Guardar como PDF" na
                        janela de impressão do navegador).
                    </p>
                </div>
            </div>

        </div>

    </div>

@endsection

{{-- ============================================================
     SCRIPTS DA VIEW
     ============================================================ --}}
@push('scripts')
<script>
    /**
     * Reenviar SMS com falha via AJAX
     *
     * @param {number} id - ID do SMS
     */
    function reenviarSms(id) {
        Swal.fire({
            title: 'Reenviar SMS?',
            text: 'Deseja reenviar este SMS?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, reenviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Reenviando...',
                text: 'Aguarde.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            const url = '{{ url("admin/sms") }}/' + id + '/reenviar';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reenviado!',
                        text: data.message || 'SMS reenviado com sucesso!',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    throw new Error(data.message || 'Erro ao reenviar SMS.');
                }
            })
            .catch(error => {
                console.error('Erro ao reenviar SMS:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: error.message || 'Não foi possível reenviar o SMS.'
                });
            });
        });
    }

    /**
     * Copia a mensagem completa para a área de transferência
     */
    function copiarMensagem() {
        const texto = document.getElementById('mensagem-completa').innerText;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(texto)
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copiado!',
                        text: 'Mensagem copiada para a área de transferência.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .catch(() => fallbackCopiar(texto));
        } else {
            fallbackCopiar(texto);
        }
    }

    /**
     * Copia o telefone para a área de transferência
     */
    function copiarTelefone() {
        const telefone = @json($sms->telefone);

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(telefone)
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copiado!',
                        text: 'Telefone copiado: ' + telefone,
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .catch(() => fallbackCopiar(telefone));
        } else {
            fallbackCopiar(telefone);
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
                timer: 2000,
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
</script>
@endpush

{{-- ============================================================
     ESTILOS PARA IMPRESSÃO
     ============================================================ --}}
@push('styles')
<style>
    @media print {
        /* Esconder sidebar, navbar e botões de ação */
        .sidebar-wrapper,
        .top-navbar,
        .btn,
        .btn-toolbar,
        .alert {
            display: none !important;
        }

        /* Ajustar margens do conteúdo */
        .main-content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .main-content {
            padding: 0 !important;
        }

        /* Cards sem sombra para impressão */
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            page-break-inside: avoid;
        }

        /* Remover fundo cinza dos cards */
        body {
            background: #fff !important;
        }
    }
</style>
@endpush