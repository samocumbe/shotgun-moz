{{--
    ============================================================
    VIEW: WhatsApp em Massa - Resultado do Lote
    ============================================================
    Ficheiro: resources/views/admin/whatsapp/massa/resultado.blade.php

    Mostra o progresso do lote (pendentes / enviados / entregues /
    lidos / falhas). Auto-refresh a cada 5s enquanto houver pendentes.
--}}

@extends('layouts.admin')

@section('title', 'Resultado do Envio - WhatsApp em Massa')

@section('content')

    {{-- ========================================================
         CABEÇALHO
         ======================================================== --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-check-circle text-success"></i> Resultado do Envio
        </h1>
        <div>
            <a href="{{ route('whatsapp.massa.index') }}" class="btn btn-sm btn-success me-2">
                <i class="bi bi-plus"></i> Novo Envio
            </a>
            <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-list"></i> Ver Todas
            </a>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        As mensagens estão a ser processadas em <strong>segundo plano</strong>.
        Recarrega esta página daqui a alguns segundos para ver o progresso.
        <br>
        <small>Lote: <code>{{ $loteId }}</code></small>
    </div>

    {{-- ========================================================
         ESTATÍSTICAS
         ======================================================== --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="number text-primary">{{ $estatisticas['total'] }}</div>
                <div class="label">Total</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="number text-success">{{ $estatisticas['enviados'] }}</div>
                <div class="label text-success">Enviados</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="number text-info">{{ $estatisticas['entregues'] }}</div>
                <div class="label text-info">Entregues</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="number text-primary">{{ $estatisticas['lidos'] }}</div>
                <div class="label text-primary">Lidos</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="number text-warning">{{ $estatisticas['pendentes'] }}</div>
                <div class="label text-warning">Pendentes</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="number text-danger">{{ $estatisticas['falhas'] }}</div>
                <div class="label text-danger">Falhas</div>
            </div>
        </div>
    </div>

    {{-- ========================================================
         LISTA DE MENSAGENS
         ======================================================== --}}
    <div class="card">
        <div class="card-body">
            @if($mensagens->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Telefone</th>
                                <th>Mensagem</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mensagens as $item)
                                <tr>
                                    <td><code>{{ $item->telefone }}</code></td>
                                    <td>{{ Str::limit($item->mensagem, 60) }}</td>
                                    <td>
                                        @switch($item->status)
                                            @case('pendente')
                                                <span class="badge bg-warning text-dark">Pendente</span>
                                                @break
                                            @case('enviado')
                                                <span class="badge bg-success">Enviado</span>
                                                @break
                                            @case('entregue')
                                                <span class="badge bg-info text-dark">Entregue</span>
                                                @break
                                            @case('lido')
                                                <span class="badge bg-primary">Lido</span>
                                                @break
                                            @case('falha')
                                                <span class="badge bg-danger">Falha</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $item->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-hourglass-split fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">A processar as mensagens...</p>
                    <button onclick="window.location.reload()" class="btn btn-sm btn-success mt-2">
                        <i class="bi bi-arrow-clockwise"></i> Recarregar
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Auto-refresh enquanto houver pendentes --}}
    @if($estatisticas['pendentes'] > 0)
        @push('scripts')
        <script>
            setTimeout(() => window.location.reload(), 5000);
        </script>
        @endpush
    @endif

@endsection