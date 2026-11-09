@extends('layouts.admin')

@section('title', 'Relatório de Check-ins - ' . $evento->nome)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório de Check-ins</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('relatorios.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('relatorios.checkins', $evento->id) }}?exportar=pdf" class="btn btn-sm btn-danger ms-2">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
    </div>
</div>

{{-- Resumo --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <small class="text-muted">Total Check-ins</small>
            <h4>{{ number_format($totais['total_checkins'], 0, ',', '.') }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <small class="text-muted">Evento</small>
            <h5>{{ $evento->nome }}</h5>
        </div>
    </div>
</div>

{{-- Tabela de Check-ins --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>QR Code</th>
                        <th>Participante</th>
                        <th>Porta</th>
                        <th>Status</th>
                        <th>Data/Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checkins as $checkin)
                    <tr>
                        <td>{{ $checkin->id }}</td>
                        <td><code>{{ $checkin->qr_code }}</code></td>
                        <td>{{ $checkin->ingresso->participante->nome_completo ?? 'N/A' }}</td>
                        <td>{{ $checkin->porta_acesso ?? 'N/A' }}</td>
                        <td>
                            @if($checkin->status == 'sincronizado')
                                <span class="badge badge-status badge-published">Sincronizado</span>
                            @else
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($checkin->data_checkin)->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Nenhum check-in encontrado para este evento.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection