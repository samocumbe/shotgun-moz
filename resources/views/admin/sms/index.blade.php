{{--
    ============================================================
    VIEW: Lista de SMS - SHOTGUN-MOZ
    ============================================================
    Exibe todos os SMS enviados com filtros, estatísticas e ações.

    Funcionalidades:
    - Cards de estatísticas (total, pendentes, enviados, falhas)
    - Filtros por status, telefone e intervalo de datas
    - Tabela paginada com todos os SMS
    - Botão para ver mensagem completa (via JSON + SweetAlert)
    - Botão para reenviar SMS com falha (via AJAX)
    - Botão para ver detalhes completos (view show)
    - Estado vazio quando não há SMS

    Dependências:
    - Layout: layouts.admin
    - Bootstrap 5, Bootstrap Icons, SweetAlert2
    - Rotas: sms.index, sms.create, sms.show, sms.show-json, sms.reenviar
--}}

@extends('layouts.admin')

@section('title', 'SMS - Shotgun MOZ')

@section('content')

    {{-- ============================================================
         CABEÇALHO DA PÁGINA
         ============================================================ --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-chat-dots text-primary"></i> SMS
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('sms.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Novo SMS
            </a>
        </div>
    </div>

    {{-- ============================================================
         CARDS DE ESTATÍSTICAS
         ============================================================ --}}
    <div class="row mb-4">
        {{-- Total de SMS --}}
        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="stat-card text-center">
                <div class="number text-primary">{{ $estatisticas['total'] ?? 0 }}</div>
                <div class="label">Total de SMS</div>
            </div>
        </div>

        {{-- Pendentes --}}
        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="stat-card text-center">
                <div class="number text-warning">{{ $estatisticas['pendentes'] ?? 0 }}</div>
                <div class="label text-warning">Pendentes</div>
            </div>
        </div>

        {{-- Enviados --}}
        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
            <div class="stat-card text-center">
                <div class="number text-success">{{ $estatisticas['enviados'] ?? 0 }}</div>
                <div class="label text-success">Enviados</div>
            </div>
        </div>

        {{-- Falhas --}}
        <div class="col-md-3 col-sm-6">
            <div class="stat-card text-center">
                <div class="number text-danger">{{ $estatisticas['falhas'] ?? 0 }}</div>
                <div class="label text-danger">Falhas</div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         FILTROS DE PESQUISA
         ============================================================ --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sms.index') }}">
                <div class="row g-3">

                    {{-- Filtro: Status --}}
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="enviado"  {{ request('status') == 'enviado'  ? 'selected' : '' }}>Enviado</option>
                            <option value="entregue" {{ request('status') == 'entregue' ? 'selected' : '' }}>Entregue</option>
                            <option value="falha"    {{ request('status') == 'falha'    ? 'selected' : '' }}>Falha</option>
                        </select>
                    </div>

                    {{-- Filtro: Telefone --}}
                    <div class="col-md-3">
                        <label class="form-label">Telefone</label>
                        <input type="text"
                               name="telefone"
                               class="form-control"
                               placeholder="84 123 4567"
                               value="{{ request('telefone') }}">
                    </div>

                    {{-- Filtro: Data Início --}}
                    <div class="col-md-2">
                        <label class="form-label">Data Início</label>
                        <input type="date"
                               name="data_inicio"
                               class="form-control"
                               value="{{ request('data_inicio') }}">
                    </div>

                    {{-- Filtro: Data Fim --}}
                    <div class="col-md-2">
                        <label class="form-label">Data Fim</label>
                        <input type="date"
                               name="data_fim"
                               class="form-control"
                               value="{{ request('data_fim') }}">
                    </div>

                    {{-- Botões de ação --}}
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="{{ route('sms.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpar
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         TABELA DE SMS
         ============================================================ --}}
    <div class="card">
        <div class="card-body">

            @if($sms->count() > 0)

                {{-- Tabela responsiva --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th style="width: 160px;">Telefone</th>
                                <th>Mensagem</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 170px;">Data/Hora</th>
                                <th style="width: 130px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sms as $item)
                                <tr>
                                    {{-- ID --}}
                                    <td>
                                        <span class="text-muted">#{{ $item->id }}</span>
                                    </td>

                                    {{-- Telefone --}}
                                    <td>
                                        <code>{{ $item->telefone }}</code>
                                    </td>

                                    {{-- Mensagem (truncada) --}}
                                    <td>
                                        {{ Str::limit($item->mensagem, 50) }}
                                        @if(strlen($item->mensagem) > 50)
                                            <button type="button"
                                                    onclick="verMensagem({{ $item->id }})"
                                                    class="btn btn-sm btn-link p-0 ms-1"
                                                    title="Ver mensagem completa">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        @endif
                                    </td>

                                    {{-- Status (badge com cor) --}}
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
                                            @case('falha')
                                                <span class="badge bg-danger">Falha</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $item->status }}</span>
                                        @endswitch
                                    </td>

                                    {{-- Data/Hora --}}
                                    <td>
                                        {{ $item->created_at->format('d/m/Y H:i:s') }}
                                        <br>
                                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                    </td>

                                    {{-- Ações --}}
                                    <td>
                                        {{-- Ver detalhes completos --}}
                                        <a href="{{ route('sms.show', $item->id) }}"
                                           class="btn btn-sm btn-info"
                                           title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Reenviar (apenas se for falha) --}}
                                        @if($item->status == 'falha')
                                            <button type="button"
                                                    onclick="reenviarSms({{ $item->id }})"
                                                    class="btn btn-sm btn-warning"
                                                    title="Reenviar SMS">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginação (mantém filtros na query string) --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $sms->appends(request()->query())->links() }}
                </div>

            @else

                {{-- Estado vazio --}}
                <div class="text-center py-5">
                    <i class="bi bi-chat-dots fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-3">Nenhum SMS encontrado.</p>
                    <a href="{{ route('sms.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Enviar primeiro SMS
                    </a>
                </div>

            @endif

        </div>
    </div>

@endsection

{{-- ============================================================
     SCRIPTS DA VIEW
     ============================================================ --}}
@push('scripts')
<script>
    /**
     * Ver mensagem completa via AJAX (JSON)
     * Chama a rota sms.show-json que devolve { mensagem, telefone, ... }
     *
     * @param {number} id - ID do SMS
     */
    function verMensagem(id) {
        // URL base gerada dinamicamente pelo Laravel
        const url = '{{ url("admin/sms") }}/' + id + '/json';

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            Swal.fire({
                title: 'Mensagem Completa',
                html: `
                    <div class="text-start">
                        <p class="mb-2"><strong>Para:</strong> <code>${data.telefone}</code></p>
                        <p class="mb-0"><strong>Mensagem:</strong></p>
                        <div style="background:#f8f9fa;padding:12px;border-radius:8px;margin-top:8px;text-align:left;">
                            ${data.mensagem}
                        </div>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Fechar',
                confirmButtonColor: '#1a73e8'
            });
        })
        .catch(error => {
            console.error('Erro ao buscar mensagem:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Não foi possível carregar a mensagem.',
                confirmButtonColor: '#1a73e8'
            });
        });
    }

    /**
     * Reenviar SMS com falha via AJAX
     * Chama a rota sms.reenviar que devolve JSON { success, message }
     *
     * @param {number} id - ID do SMS
     */
    function reenviarSms(id) {
        Swal.fire({
            title: 'Reenviar SMS?',
            text: 'Deseja reenviar este SMS?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, reenviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            // Mostrar loading
            Swal.fire({
                title: 'Reenviando...',
                text: 'Aguarde.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            // URL base gerada dinamicamente
            const url = '{{ url("admin/sms") }}/' + id + '/reenviar';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reenviado!',
                        text: data.message || 'SMS reenviado com sucesso!',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    throw new Error(data.message || 'Erro ao reenviar SMS.');
                }
            })
            .catch(error => {
                console.error('Erro ao reenviar SMS:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: error.message || 'Não foi possível reenviar o SMS.'
                });
            });
        });
    }
</script>
@endpush