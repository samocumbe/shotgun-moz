{{--
    ============================================================
    VIEW: Lista de Check-ins
    ============================================================
    Exibe todos os check-ins realizados em eventos.
    Permite filtrar por evento e visualizar detalhes.
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Check-ins - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-check2-circle text-success"></i> Check-ins</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

{{-- ============================================================
    FILTROS
============================================================ --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('checkins.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="evento_id" class="form-label">Evento</label>
                    <select name="evento_id" id="evento_id" class="form-select">
                        <option value="">Todos os eventos</option>
                        @foreach($eventos as $evento)
                            <option value="{{ $evento->id }}" {{ request('evento_id') == $evento->id ? 'selected' : '' }}>
                                {{ $evento->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="sincronizado" {{ request('status') == 'sincronizado' ? 'selected' : '' }}>Sincronizado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('checkins.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
    ESTATÍSTICAS RÁPIDAS
============================================================ --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="number">{{ $totalCheckins ?? 0 }}</div>
                    <div class="label">Total Check-ins</div>
                </div>
                <div class="icon text-primary">
                    <i class="bi bi-check2-all"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="number text-warning">{{ $pendentes ?? 0 }}</div>
                    <div class="label">Pendentes</div>
                </div>
                <div class="icon text-warning">
                    <i class="bi bi-clock"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="number text-success">{{ $sincronizados ?? 0 }}</div>
                    <div class="label">Sincronizados</div>
                </div>
                <div class="icon text-success">
                    <i class="bi bi-cloud-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="number text-info">{{ $checkinsHoje ?? 0 }}</div>
                    <div class="label">Hoje</div>
                </div>
                <div class="icon text-info">
                    <i class="bi bi-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
    TABELA DE CHECK-INS (SEM DATATABLES - ESTÁTICA)
============================================================ --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list"></i> Lista de Check-ins</h5>
        <span class="badge bg-primary">{{ $checkins->total() ?? 0 }} registros</span>
    </div>
    <div class="card-body">
        @if($checkins->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Evento</th>
                            <th>Ingresso</th>
                            <th>Participante</th>
                            <th>Data/Hora</th>
                            <th>Dispositivo</th>
                            <th>Status</th>
                            <th style="width: 60px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checkins as $checkin)
                        <tr>
                            <td>{{ $checkin->id }}</td>
                            <td>
                                <span class="badge bg-info">{{ $checkin->evento->nome ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <code>{{ $checkin->ingresso->qr_code ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <strong>{{ $checkin->ingresso->participante->nome_completo ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $checkin->ingresso->participante->email ?? '' }}</small>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($checkin->data_checkin)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                {{ $checkin->dispositivo->nome ?? 'N/A' }}
                                @if($checkin->dispositivo)
                                    <br><small class="text-muted">{{ $checkin->dispositivo->modelo ?? '' }}</small>
                                @endif
                            </td>
                            <td>
                                @if($checkin->status == 'sincronizado')
                                    <span class="badge bg-success">Sincronizado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="verDetalhes({{ $checkin->id }})" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Paginação --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $checkins->appends(request()->query())->links() }}
            </div>
        @else
            {{-- MENSAGEM QUANDO NÃO HÁ CHECK-INS --}}
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3 text-muted"></i>
                <h5 class="text-muted">Nenhum check-in encontrado</h5>
                <p class="text-muted">
                    @if(request()->has('evento_id') || request()->has('status') || request()->has('data_inicio'))
                        Tente ajustar os filtros para ver mais resultados.
                    @else
                        Ainda não há check-ins registrados no sistema.
                        <br>
                        <small>Os check-ins são criados automaticamente quando um ingresso é validado na entrada.</small>
                    @endif
                </p>
                @if(request()->has('evento_id') || request()->has('status') || request()->has('data_inicio'))
                    <a href="{{ route('checkins.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpar filtros
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    /**
     * Ver detalhes de um check-in
     */
    function verDetalhes(id) {
        Swal.fire({
            title: 'Carregando...',
            text: 'Aguarde enquanto carregamos os detalhes.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('/admin/checkins/' + id)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Erro ao carregar detalhes');
            }

            const checkin = data.data;
            
            let html = `
                <div class="text-start">
                    <div class="row mb-2">
                        <div class="col-6"><strong>ID:</strong></div>
                        <div class="col-6">#${checkin.id}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Evento:</strong></div>
                        <div class="col-6">${checkin.evento?.nome || 'N/A'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Participante:</strong></div>
                        <div class="col-6">${checkin.ingresso?.participante?.nome_completo || 'N/A'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Email:</strong></div>
                        <div class="col-6">${checkin.ingresso?.participante?.email || 'N/A'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>QR Code:</strong></div>
                        <div class="col-6"><code>${checkin.qr_code || 'N/A'}</code></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Data/Hora:</strong></div>
                        <div class="col-6">${new Date(checkin.data_checkin).toLocaleString('pt-BR')}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Dispositivo:</strong></div>
                        <div class="col-6">${checkin.dispositivo?.nome || 'N/A'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Porta de Acesso:</strong></div>
                        <div class="col-6">${checkin.porta_acesso || 'N/A'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Localização:</strong></div>
                        <div class="col-6">${checkin.localizacao || 'N/A'}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Status:</strong></div>
                        <div class="col-6">
                            ${checkin.status === 'sincronizado' 
                                ? '<span class="badge bg-success">Sincronizado</span>' 
                                : '<span class="badge bg-warning text-dark">Pendente</span>'}
                        </div>
                    </div>
                </div>
            `;

            Swal.fire({
                title: '📋 Detalhes do Check-in',
                html: html,
                width: 600,
                confirmButtonText: 'Fechar',
                confirmButtonColor: '#3085d6'
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: error.message || 'Não foi possível carregar os detalhes.'
            });
        });
    }

    // ============================================================
    // FILTRO AUTOMÁTICO - Atualiza ao mudar o evento
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const eventoSelect = document.getElementById('evento_id');
        if (eventoSelect) {
            eventoSelect.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
</script>
@endpush