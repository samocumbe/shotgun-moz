{{--
    ============================================================
    VIEW: Envio em Massa - Pré-visualização
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Pré-visualização - Envio em Massa')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-eye text-primary"></i> Pré-visualização
            <small class="text-muted fs-6">— {{ count($mensagens) }} SMS</small>
        </h1>
        <a href="{{ route('sms.massa.criar', $evento->id) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Atenção:</strong> Depois de enviares, os SMS <u>não podem ser cancelados</u>.
        Confirma tudo antes de clicar em <strong>"Enviar Todos"</strong>.
    </div>

    <div class="row">

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-dots"></i>
                        Mensagens ({{ count($mensagens) }})
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">

                    @foreach($mensagens as $i => $msg)
                        <div class="mb-3 p-3 border rounded" style="background: #f8f9fa;">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong>{{ $msg['nome'] }}</strong>
                                    <code class="ms-2">{{ $msg['telefone'] }}</code>
                                </div>
                                <small class="text-muted">#{{ $i + 1 }}</small>
                            </div>
                            <div class="p-2 bg-white rounded">
                                {{ $msg['mensagem'] }}
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Resumo</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Evento:</strong> {{ $evento->nome }}</p>
                    <p class="mb-2"><strong>Total de SMS:</strong> {{ count($mensagens) }}</p>
                    <p class="mb-0"><strong>Template:</strong>
                        @if($request->filled('template_id'))
                            Sim
                        @else
                            Personalizado
                        @endif
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('sms.massa.enviar') }}" id="form-enviar">
                @csrf
                <input type="hidden" name="evento_id" value="{{ $evento->id }}">

                @foreach($mensagens as $i => $msg)
                    <input type="hidden" name="mensagens[{{ $i }}][telefone]" value="{{ $msg['telefone'] }}">
                    <input type="hidden" name="mensagens[{{ $i }}][mensagem]" value="{{ $msg['mensagem'] }}">
                    <input type="hidden" name="mensagens[{{ $i }}][nome]"     value="{{ $msg['nome'] }}">
                    <input type="hidden" name="mensagens[{{ $i }}][id]"       value="{{ $msg['id'] }}">
                @endforeach

                <button type="button" class="btn btn-success w-100 mb-2" onclick="confirmarEnvio()">
                    <i class="bi bi-send-check"></i> Enviar Todos os {{ count($mensagens) }} SMS
                </button>

                <a href="{{ route('sms.massa.criar', $evento->id) }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Voltar e Editar
                </a>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function confirmarEnvio() {
        Swal.fire({
            title: 'Confirmar envio?',
            html: `Vais enviar <strong>{{ count($mensagens) }} SMS</strong>.<br>Esta ação não pode ser cancelada.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, enviar todos!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enviando...',
                    text: 'Os SMS estão a ser colocados na fila.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
                document.getElementById('form-enviar').submit();
            }
        });
    }
</script>
@endpush