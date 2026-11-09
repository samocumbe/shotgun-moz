{{--
    ============================================================
    VIEW: Detalhes do WhatsApp - SHOTGUN-MOZ
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do WhatsApp - Shotgun MOZ')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-whatsapp text-success"></i>
            Detalhes da Mensagem <span class="text-muted">#{{ $whatsapp->id }}</span>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
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
                        <div class="col-md-6 mb-2"><strong>ID:</strong> #{{ $whatsapp->id }}</div>
                        <div class="col-md-6 mb-2"><strong>UUID:</strong> <code>{{ $whatsapp->uuid }}</code></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Telefone:</strong> <code>{{ $whatsapp->telefone }}</code>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Status:</strong>
                            @switch($whatsapp->status)
                                @case('pendente') <span class="badge bg-warning text-dark">Pendente</span> @break
                                @case('enviado')  <span class="badge bg-success">Enviado</span> @break
                                @case('entregue') <span class="badge bg-info text-dark">Entregue</span> @break
                                @case('lido')     <span class="badge bg-primary">Lido</span> @break
                                @case('falha')    <span class="badge bg-danger">Falha</span> @break
                            @endswitch
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2"><strong>Provedor:</strong> {{ $whatsapp->provedor ?? 'N/A' }}</div>
                        <div class="col-md-6 mb-2"><strong>Referência:</strong> {{ $whatsapp->referencia ?? 'N/A' }}</div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Criado em:</strong> {{ $whatsapp->created_at->format('d/m/Y H:i:s') }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Enviado em:</strong>
                            {{ $whatsapp->data_envio ? $whatsapp->data_envio->format('d/m/Y H:i:s') : 'N/A' }}
                        </div>
                    </div>
                    @if($whatsapp->data_entrega)
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Entregue em:</strong> {{ $whatsapp->data_entrega->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    @endif
                    @if($whatsapp->data_leitura)
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Lido em:</strong> {{ $whatsapp->data_leitura->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    @endif

                    @if($whatsapp->erro)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-danger mb-0">
                                    <strong>Erro:</strong> {{ $whatsapp->erro }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($whatsapp->evento)
                        <div class="row mt-3">
                            <div class="col-12">
                                <strong>Evento:</strong>
                                <a href="{{ route('eventos.show', $whatsapp->evento->id) }}">
                                    {{ $whatsapp->evento->nome }}
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($whatsapp->organizacao)
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Organização:</strong> {{ $whatsapp->organizacao->nome }}
                            </div>
                        </div>
                    @endif

                    @if($whatsapp->user)
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Enviado por:</strong> {{ $whatsapp->user->name }}
                                <small class="text-muted">({{ $whatsapp->user->email }})</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat"></i> Mensagem</h5>
                </div>
                <div class="card-body">
                    <div id="mensagem-completa"
                         style="background:#f8f9fa;padding:15px;border-radius:8px;white-space:pre-wrap;word-wrap:break-word;">
                        {{ $whatsapp->mensagem }}
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            {{ strlen($whatsapp->mensagem) }} caracteres
                        </small>
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
                    @if($whatsapp->status == 'falha')
                        <button type="button" onclick="reenviarWhatsApp({{ $whatsapp->id }})"
                                class="btn btn-warning">
                            <i class="bi bi-arrow-repeat"></i> Reenviar
                        </button>
                    @endif
                    <button type="button" onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <a href="{{ route('whatsapp.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar à lista
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function reenviarWhatsApp(id) {
        Swal.fire({
            title: 'Reenviar WhatsApp?',
            text: 'Deseja reenviar esta mensagem?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#25D366',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, reenviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Reenviando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const url = '{{ url("admin/whatsapp") }}/' + id + '/reenviar';

            fetch(url, {
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
                        title: 'Reenviado!',
                        text: data.message,
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(err => Swal.fire({ icon:'error', title:'Erro!', text: err.message }));
        });
    }
</script>
@endpush