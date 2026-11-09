{{--
    ============================================================
    VIEW: Envio em Massa - Escolher Evento
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Envio em Massa - Shotgun MOZ')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-send-plus text-primary"></i> Envio em Massa
        </h1>
        <a href="{{ route('sms.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar a SMS
        </a>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Como funciona:</strong>
        Escolhe um evento abaixo, seleciona os clientes que queres notificar
        e envia o mesmo SMS para todos de uma só vez.
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Escolhe o Evento</h5>
        </div>
        <div class="card-body">

            @if($eventos->count() > 0)
                <div class="row g-3">
                    @foreach($eventos as $evento)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border">
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title mb-2">
                                        <i class="bi bi-calendar-event text-primary"></i>
                                        {{ $evento->nome }}
                                    </h6>
                                    <p class="card-text small text-muted mb-1">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $evento->local->nome ?? 'Local não definido' }}
                                    </p>
                                    <p class="card-text small text-muted mb-3">
                                        <i class="bi bi-clock"></i>
                                        {{ $evento->data_inicio ? $evento->data_inicio->format('d/m/Y') : 'Sem data' }}
                                    </p>

                                    <a href="{{ route('sms.massa.criar', $evento->id) }}"
                                       class="btn btn-sm btn-primary w-100 mt-auto">
                                        <i class="bi bi-arrow-right"></i> Escolher Clientes
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $eventos->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">Nenhum evento publicado encontrado.</p>
                </div>
            @endif

        </div>
    </div>

@endsection