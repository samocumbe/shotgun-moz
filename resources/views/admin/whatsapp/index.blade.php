{{--
    ============================================================
    VIEW: Lista de WhatsApp - SHOTGUN-MOZ
    ============================================================
    Exibe todas as mensagens WhatsApp enviadas com filtros,
    estatísticas e ações.

    ALTERAÇÃO:
    - Botão "Envio em Massa" adicionado ao cabeçalho, à esquerda
      do botão "Nova Mensagem".

    Estrutura:
    1. Cabeçalho (título + botões de ação)
    2. Cards de estatísticas (Total / Enviados / Lidos / Falhas)
    3. Filtros (status, telefone, datas)
    4. Tabela paginada de mensagens
    5. Scripts (ver mensagem completa + reenviar)
--}}

@extends('layouts.admin')

@section('title', 'WhatsApp - Shotgun MOZ')

@section('content')

    {{-- ========================================================
         1. CABEÇALHO
         ========================================================
         - À esquerda: título "WhatsApp"
         - À direita: dois botões
             • "Envio em Massa" (novo) → whatsapp.massa.index
             • "Nova Mensagem" (existente) → whatsapp.create
    --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-whatsapp text-success"></i> WhatsApp
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0 gap-2">

            {{-- ⬇️ NOVO BOTÃO: Envio em Massa --}}
            <a href="{{ route('whatsapp.massa.index') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-send-plus"></i> Envio em Massa
            </a>

            {{-- Botão existente: Nova Mensagem --}}
            <a href="{{ route('whatsapp.create') }}" class="btn btn-sm btn-success">
                <i class="bi bi-plus"></i> Nova Mensagem
            </a>
        </div>
    </div>

    {{-- ========================================================
         2. ESTATÍSTICAS
         ========================================================
         4 cards: Total | Enviados | Lidos | Falhas
         Os valores vêm do WhatsAppController::index() → $estatisticas
    --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="number text-primary">{{ $estatisticas['total'] ?? 0 }}</div>
                <div class="label">Total</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="number text-success">{{ $estatisticas['enviados'] ?? 0 }}</div>
                <div class="label text-success">Enviados</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="number text-info">{{ $estatisticas['lidos'] ?? 0 }}</div>
                <div class="label text-info">Lidos</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="number text-danger">{{ $estatisticas['falhas'] ?? 0 }}</div>
                <div class="label text-danger">Falhas</div>
            </div>
        </div>
    </div>

    {{-- ========================================================
         3. FILTROS
         ========================================================
         Filtra por: status, telefone, data_inicio, data_fim
         Os valores do formulário são preservados via request('...')
    --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('whatsapp.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="enviado"  {{ request('status') == 'enviado'  ? 'selected' : '' }}>Enviado</option>
                            <option value="entregue" {{ request('status') == 'entregue' ? 'selected' : '' }}>Entregue</option>
                            <option value="lido"     {{ request('status') == 'lido'     ? 'selected' : '' }}>Lido</option>
                            <option value="falha"    {{ request('status') == 'falha'    ? 'selected' : '' }}>Falha</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control"
                               placeholder="84 123 4567" value="{{ request('telefone') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="{{ route('whatsapp.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================
         4. TABELA DE MENSAGENS
         ========================================================
         - Colunas: ID, Telefone, Mensagem, Status, Data/Hora, Ações
         - Se a mensagem for > 50 chars → botão "olho" abre modal via AJAX
         - Se status = falha → botão "reenviar" (AJAX POST)
         - Paginação preserva os filtros com ->appends(request()->query())
    --}}
    <div class="card">
        <div class="card-body">
            @if($whatsapp->count() > 0)
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
                            @foreach($whatsapp as $item)
                                <tr>
                                    {{-- ID --}}
                                    <td><span class="text-muted">#{{ $item->id }}</span></td>

                                    {{-- Telefone --}}
                                    <td><code>{{ $item->telefone }}</code></td>

                                    {{-- Mensagem (truncada) + botão ver completa --}}
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

                                    {{-- Status com badge colorida --}}
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

                                    {{-- Data/hora absoluta + relativa --}}
                                    <td>
                                        {{ $item->created_at->format('d/m/Y H:i:s') }}
                                        <br>
                                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                    </td>

                                    {{-- Ações: ver detalhes + reenviar (só se falha) --}}
                                    <td>
                                        <a href="{{ route('whatsapp.show', $item->id) }}"
                                           class="btn btn-sm btn-info" title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($item->status == 'falha')
                                            <button type="button"
                                                    onclick="reenviarWhatsApp({{ $item->id }})"
                                                    class="btn btn-sm btn-warning" title="Reenviar">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginação --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $whatsapp->appends(request()->query())->links() }}
                </div>
            @else
                {{-- Estado vazio --}}
                <div class="text-center py-5">
                    <i class="bi bi-whatsapp fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-3">Nenhuma mensagem WhatsApp encontrada.</p>
                    <a href="{{ route('whatsapp.create') }}" class="btn btn-success">
                        <i class="bi bi-plus"></i> Enviar primeira mensagem
                    </a>
                </div>
            @endif
        </div>
    </div>

@endsection

{{-- ========================================================
     5. SCRIPTS
     ========================================================
     - verMensagem(id):     GET  /admin/whatsapp/{id}/json   → modal Swal
     - reenviarWhatsApp(id): POST /admin/whatsapp/{id}/reenviar → Swal + reload
--}}
@push('scripts')
<script>
    /**
     * Abre modal com a mensagem completa
     * Usa o endpoint JSON: whatsapp.show-json
     */
    function verMensagem(id) {
        const url = '{{ url("admin/whatsapp") }}/' + id + '/json';

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            Swal.fire({
                title: 'Mensagem WhatsApp',
                html: `
                    <div class="text-start">
                        <p class="mb-2"><strong>Para:</strong> <code>${data.telefone}</code></p>
                        <p class="mb-0"><strong>Mensagem:</strong></p>
                        <div style="background:#f8f9fa;padding:12px;border-radius:8px;margin-top:8px;white-space:pre-wrap;text-align:left;">
                            ${data.mensagem}
                        </div>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Fechar',
                confirmButtonColor: '#25D366'
            });
        })
        .catch(() => Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Não foi possível carregar.'
        }));
    }

    /**
     * Reenvia uma mensagem com falha
     * Usa o endpoint: whatsapp.reenviar
     */
    function reenviarWhatsApp(id) {
        Swal.fire({
            title: 'Reenviar WhatsApp?',
            text: 'Deseja reenviar esta mensagem?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#25D366',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, reenviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Reenviando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const url = '{{ url("admin/whatsapp") }}/' + id + '/reenviar';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reenviado!',
                        text: data.message,
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    throw new Error(data.message || 'Erro ao reenviar.');
                }
            })
            .catch(err => Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: err.message
            }));
        });
    }
</script>
@endpush