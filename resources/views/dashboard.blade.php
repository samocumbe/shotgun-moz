@extends('layouts.admin')

@section('title', 'Dashboard - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download"></i> Exportar
            </button>
        </div>
        <a href="{{ route('eventos.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Novo Evento
        </a>
    </div>
</div>

<!-- Cards Estatísticos -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Eventos Ativos</div>
                    <div class="number">{{ $eventos_ativos ?? 0 }}</div>
                </div>
                <div class="icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-success">
                    <i class="bi bi-arrow-up"></i> {{ $eventos_ativos_mes ?? 0 }} este mês
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Ingressos Vendidos</div>
                    <div class="number">{{ $ingressos_vendidos ?? 0 }}</div>
                </div>
                <div class="icon text-success">
                    <i class="bi bi-ticket"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-success">
                    <i class="bi bi-arrow-up"></i> +{{ $ingressos_hoje ?? 0 }} hoje
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Check-ins Hoje</div>
                    <div class="number">{{ $checkins_hoje ?? 0 }}</div>
                </div>
                <div class="icon text-info">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-info">
                    <i class="bi bi-clock"></i> {{ $checkins_hora ?? 'Sem dados' }}
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="label">Receita Total</div>
                    <div class="number">{{ isset($receita_total) ? number_format($receita_total, 2, ',', '.') : '0,00' }} MZN</div>
                </div>
                <div class="icon text-warning">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-success">
                    <i class="bi bi-arrow-up"></i> +{{ isset($receita_mes) ? number_format($receita_mes, 2, ',', '.') : '0,00' }} MZN este mês
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Vendas por Dia
            </div>
            <div class="card-body">
                <canvas id="vendasChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart"></i> Distribuição de Vendas
            </div>
            <div class="card-body">
                <canvas id="distribuicaoChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Últimos Eventos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-week"></i> Últimos Eventos</span>
                <a href="{{ route('eventos.index') }}" class="btn btn-sm btn-primary">
                    Ver Todos <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Local</th>
                                <th>Data</th>
                                <th>Ingressos</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($eventos_recentes ?? [] as $evento)
                            <tr>
                                <td>{{ $evento->nome }}</td>
                                <td>{{ $evento->local->nome ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y H:i') }}</td>
                                <td>{{ $evento->ingressos_vendidos }}/{{ $evento->capacidade_total }}</td>
                                <td>
                                    <span class="badge badge-status 
                                        @if($evento->status == 'publicado') badge-published
                                        @elseif($evento->status == 'rascunho') badge-draft
                                        @elseif($evento->status == 'cancelado') badge-canceled
                                        @else badge-ended @endif">
                                        {{ ucfirst($evento->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nenhum evento cadastrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimos Check-ins -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Últimos Check-ins</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ingresso</th>
                                <th>Participante</th>
                                <th>Evento</th>
                                <th>Horário</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($checkins_recentes ?? [] as $checkin)
                            <tr>
                                <td><code>{{ $checkin->qr_code }}</code></td>
                                <td>{{ $checkin->ingresso->participante->nome_completo ?? 'N/A' }}</td>
                                <td>{{ $checkin->evento->nome ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($checkin->data_checkin)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-status {{ $checkin->status == 'sincronizado' ? 'badge-published' : 'badge-draft' }}">
                                        {{ ucfirst($checkin->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Nenhum check-in registrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Vendas
    const vendasCtx = document.getElementById('vendasChart').getContext('2d');
    new Chart(vendasCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($vendas_labels ?? []) !!},
            datasets: [{
                label: 'Ingressos Vendidos',
                data: {!! json_encode($vendas_dados ?? []) !!},
                borderColor: 'rgb(52, 152, 219)',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Gráfico de Distribuição
    const distribuicaoCtx = document.getElementById('distribuicaoChart').getContext('2d');
    new Chart(distribuicaoCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($distribuicao_labels ?? ['Normal', 'VIP', 'Backstage']) !!},
            datasets: [{
                data: {!! json_encode($distribuicao_dados ?? [60, 25, 15]) !!},
                backgroundColor: ['#3498db', '#2ecc71', '#f39c12'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection