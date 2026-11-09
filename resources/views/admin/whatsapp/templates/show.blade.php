{{--
    ============================================================
    VIEW: Detalhes do Template WhatsApp - SHOTGUN-MOZ
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Template - Shotgun MOZ')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-file-earmark-text text-success"></i>
            Template <span class="text-muted">#{{ $template->id }}</span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-sm btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a href="{{ route('whatsapp.templates.edit', $template->id) }}" class="btn btn-sm btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2"><strong>Nome:</strong> {{ $template->nome }}</div>
                        <div class="col-md-6 mb-2"><strong>Código:</strong> <code>{{ $template->codigo }}</code></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Categoria:</strong>
                            @switch($template->categoria)
                                @case('marketing') <span class="badge bg-primary">Marketing</span> @break
                                @case('utilidade') <span class="badge bg-info text-dark">Utilidade</span> @break
                                @case('autenticacao') <span class="badge bg-secondary">Autenticação</span> @break
                            @endswitch
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Status:</strong>
                            @switch($template->status)
                                @case('aprovado') <span class="badge bg-success">Aprovado</span> @break
                                @case('pendente') <span class="badge bg-warning text-dark">Pendente</span> @break
                                @case('rejeitado') <span class="badge bg-danger">Rejeitado</span> @break
                            @endswitch
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Estado:</strong>
                            @if($template->is_active)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-secondary">Inativo</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Criado em:</strong> {{ $template->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    @if($template->organizacao)
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Organização:</strong> {{ $template->organizacao->nome }}
                            </div>
                        </div>
                    @endif

                    <hr>

                    <strong>Variáveis:</strong>
                    <div class="mt-2">
                        @if(!empty($template->variaveis))
                            @foreach($template->variaveis as $v)
                                <span class="badge bg-primary me-1"><code>{{$v}}</code></span>
                            @endforeach
                        @else
                            <em class="text-muted">Nenhuma variável detetada</em>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat"></i> Mensagem</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewTemplate()">
                        <i class="bi bi-eye"></i> Pré-visualizar
                    </button>
                </div>
                <div class="card-body">
                    <div id="mensagem-template"
                         style="background:#f8f9fa;padding:15px;border-radius:8px;white-space:pre-wrap;word-wrap:break-word;">
                        {{ $template->mensagem }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <button type="button" onclick="toggleAtivo()" class="btn btn-outline-primary">
                        <i class="bi bi-toggle-on"></i>
                        {{ $template->is_active ? 'Desativar' : 'Ativar' }}
                    </button>
                    <button type="button" onclick="copiarMensagem()" class="btn btn-outline-secondary">
                        <i class="bi bi-clipboard"></i> Copiar Mensagem
                    </button>
                    <a href="{{ route('whatsapp.templates.edit', $template->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const templateId = {{ $template->id }};

    /**
     * Pré-visualizar o template com valores de exemplo
     */
    function previewTemplate() {
        fetch('{{ url("admin/whatsapp/templates") }}/' + templateId + '/preview-json', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            Swal.fire({
                title: 'Pré-visualização',
                html: `<div style="background:#e7ffdb;padding:15px;border-radius:8px;white-space:pre-wrap;text-align:left;">${data.renderizado.replace(/\n/g, '<br>')}</div>`,
                icon: 'info',
                confirmButtonText: 'Fechar',
                confirmButtonColor: '#25D366',
                width: 600
            });
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Erro!', text: 'Não foi possível pré-visualizar.' }));
    }

    /**
     * Alternar ativo/inativo
     */
    function toggleAtivo() {
        fetch('{{ url("admin/whatsapp/templates") }}/' + templateId + '/toggle-ativo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Atualizado!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            }
        })
        .catch(err => Swal.fire({ icon: 'error', title: 'Erro!', text: 'Não foi possível alterar.' }));
    }

    /**
     * Copiar mensagem para a área de transferência
     */
    function copiarMensagem() {
        const texto = document.getElementById('mensagem-template').innerText;
        navigator.clipboard.writeText(texto).then(() => {
            Swal.fire({
                icon: 'success', title: 'Copiado!',
                timer: 1500, showConfirmButton: false
            });
        });
    }
</script>
@endpush