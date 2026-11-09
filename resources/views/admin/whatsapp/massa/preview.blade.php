@extends('layouts.admin')

@section('title', 'Pré-visualização - WhatsApp em Massa')

@section('content')

    {{-- ========================================================
         CABEÇALHO
         ======================================================== --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-eye text-success"></i>
            Pré-visualização
            <small class="text-muted fs-6">— {{ $evento->nome }}</small>
        </h1>
        <a href="{{ route('whatsapp.massa.criar', $evento->id) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar e editar
        </a>
    </div>

    {{-- Aviso --}}
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        Vais enviar <strong>{{ count($mensagens) }}</strong> mensagens WhatsApp.
        Confirma a pré-visualização abaixo antes de continuar.
        <br>
        <small>
            <i class="bi bi-info-circle"></i>
            Se vires variáveis a vermelho numa das mensagens, significa que
            o template tem variáveis desconhecidas. Volta atrás e corrige.
        </small>
    </div>

    {{-- Formulário de envio --}}
    <form method="POST" action="{{ route('whatsapp.massa.enviar') }}" id="form-enviar">
        @csrf
        <input type="hidden" name="evento_id" value="{{ $evento->id }}">
        <input type="hidden" name="template_id" value="{{ $template_id }}">

        {{-- Cada mensagem como hidden input --}}
        @foreach($mensagens as $i => $m)
            <input type="hidden" name="mensagens[{{ $i }}][telefone]" value="{{ $m['telefone'] }}">
            <input type="hidden" name="mensagens[{{ $i }}][mensagem]" value="{{ $m['mensagem'] }}">
        @endforeach

        {{-- Grelha de pré-visualizações --}}
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    @foreach($mensagens as $m)
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>{{ $m['nome'] }}</strong>
                                    <code class="small">{{ $m['telefone'] }}</code>
                                </div>
                                <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $m['mensagem'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('whatsapp.massa.criar', $evento->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-send"></i> Confirmar e Enviar {{ count($mensagens) }} WhatsApp
            </button>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    document.getElementById('form-enviar').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Confirmar envio?',
            text: '{{ count($mensagens) }} mensagens vão ser colocadas na fila.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#25D366',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, enviar',
            cancelButtonText: 'Cancelar'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
</script>
@endpush