{{--
    ============================================================
    VIEW: QR Code do Ingresso
    ============================================================
    Exibe o QR Code do ingresso para impressão.
--}}

@extends('layouts.admin')

@section('title', 'QR Code - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-qr-code text-primary"></i> QR Code</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('ingressos.show', ['eventoId' => $ingresso->evento_id, 'id' => $ingresso->id]) }}" 
           class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary ms-2">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card text-center">
            <div class="card-header">
                <h5 class="mb-0">{{ $ingresso->evento->nome ?? 'N/A' }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-4 p-4 bg-white d-inline-block rounded shadow-sm">
                    {!! $qrCode !!}
                </div>

                <div class="mt-3">
                    <p><strong>Código:</strong></p>
                    <h4><code class="bg-light p-2 rounded">{{ $ingresso->qr_code }}</code></h4>
                </div>

                <hr>

                <div class="row text-start">
                    <div class="col-md-6">
                        <small class="text-muted">Evento</small>
                        <p class="fw-semibold">{{ $ingresso->evento->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Categoria</small>
                        <p class="fw-semibold">{{ $ingresso->categoria->nome ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row text-start">
                    <div class="col-md-6">
                        <small class="text-muted">Cliente</small>
                        <p class="fw-semibold">{{ $ingresso->participante->nome_completo ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Status</small>
                        <p>
                            @if($ingresso->status == 'pago')
                                <span class="badge badge-status badge-published">Pago</span>
                            @elseif($ingresso->status == 'utilizado')
                                <span class="badge badge-status badge-ended">Utilizado</span>
                            @else
                                <span class="badge badge-status badge-draft">{{ ucfirst($ingresso->status) }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <small>Apresente este QR Code na entrada do evento.</small>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .sidebar-wrapper { display: none !important; }
        .top-navbar { display: none !important; }
        .main-content-wrapper { margin-left: 0 !important; padding-top: 0 !important; }
        .main-content { padding: 20px !important; }
        .btn-toolbar { display: none !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
        .card-footer { display: none !important; }
    }
</style>
@endpush
@endsection