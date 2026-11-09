{{--
    ============================================================
    VIEW: QR Code do Ingresso
    ============================================================
    Exibe o QR Code do ingresso para acesso ao evento.
--}}

@extends('layouts.admin')

@section('title', 'QR Code - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-qr-code text-primary"></i> QR Code</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('carteira.index') }}" class="btn btn-sm btn-secondary">
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
                <h5 class="mb-0">{{ $item->ingresso->evento->nome ?? 'N/A' }}</h5>
            </div>
            <div class="card-body">
                {{-- QR Code --}}
                <div class="mb-4 p-4 bg-white d-inline-block rounded shadow-sm">
                    {!! $qrCode !!}
                </div>

                <div class="mt-3">
                    <p><strong>Código de Acesso:</strong></p>
                    <h4><code class="bg-light p-2 rounded">{{ $item->codigo_acesso }}</code></h4>
                </div>

                <hr>

                <div class="row text-start">
                    <div class="col-md-6">
                        <small class="text-muted">Evento</small>
                        <p class="fw-semibold">{{ $item->ingresso->evento->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Categoria</small>
                        <p class="fw-semibold">{{ $item->ingresso->categoria->nome ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row text-start">
                    <div class="col-md-6">
                        <small class="text-muted">Data</small>
                        <p class="fw-semibold">
                            {{ \Carbon\Carbon::parse($item->ingresso->evento->data_inicio ?? now())->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Horário</small>
                        <p class="fw-semibold">
                            {{ $item->ingresso->evento->hora_inicio ?? 'N/A' }} - {{ $item->ingresso->evento->hora_fim ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="row text-start">
                    <div class="col-md-12">
                        <small class="text-muted">Local</small>
                        <p class="fw-semibold">{{ $item->ingresso->evento->local->nome ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <span class="badge badge-status 
                        @if($item->status == 'ativo') badge-published
                        @elseif($item->status == 'utilizado') badge-ended
                        @elseif($item->status == 'transferido') badge-draft
                        @else badge-canceled @endif">
                        <i class="bi 
                            @if($item->status == 'ativo') bi-check-circle
                            @elseif($item->status == 'utilizado') bi-check2-circle
                            @elseif($item->status == 'transferido') bi-arrow-right
                            @else bi-x-circle @endif">
                        </i>
                        {{ ucfirst($item->status) }}
                    </span>
                </div>
            </div>
            <div class="card-footer text-muted">
                <small>Apresente este QR Code na entrada do evento.</small>
            </div>
        </div>
    </div>
</div>

{{-- CSS para impressão --}}
@push('styles')
<style>
    @media print {
        .sidebar-wrapper {
            display: none !important;
        }
        .top-navbar {
            display: none !important;
        }
        .main-content-wrapper {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }
        .main-content {
            padding: 20px !important;
        }
        .btn-toolbar {
            display: none !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        .card-footer {
            display: none !important;
        }
        .badge-status {
            border: 1px solid #ddd !important;
        }
    }
</style>
@endpush