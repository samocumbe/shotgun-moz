{{--
    ============================================================
    VIEW: Relatório de Check-ins
    ============================================================
    Exibe o relatório de check-ins com filtros e opções de exportação.
--}}

@extends('layouts.admin')

@section('title', 'Relatório de Check-ins - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório de Check-ins</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('relatorios.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    <strong>Selecione um evento</strong> para visualizar o relatório de check-ins.
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('relatorios.checkins', ['eventoId' => 0]) }}" method="GET" class="row g-3">
            <div class="col-md-8">
                <label for="evento_id" class="form-label">Selecione o Evento</label>
                <select class="form-select" id="evento_id" name="evento_id" required>
                    <option value="">Selecione um evento...</option>
                    @foreach($eventos ?? [] as $evento)
                        <option value="{{ $evento->id }}" {{ request('evento_id') == $evento->id ? 'selected' : '' }}>
                            {{ $evento->nome }} - {{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-search"></i> Gerar Relatório
                </button>
            </div>
        </form>
    </div>
</div>
@endsection