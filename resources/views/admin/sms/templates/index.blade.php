{{--
    ============================================================
    VIEW: Lista de Templates SMS - SHOTGUN-MOZ
    ============================================================
    Exibe todos os templates de mensagens SMS com filtros e ações.

    Estrutura da tabela sms_templates:
    - id, uuid, organizacao_id, nome, assunto, mensagem, tipo,
      variaveis, is_active, created_at, updated_at

    Funcionalidades:
    - Filtros por busca, tipo e estado (ativo/inativo)
    - Tabela paginada com todos os templates
    - Toggle de ativo/inativo via AJAX
    - Botões: ver, editar, excluir
    - Estado vazio quando não há templates
--}}

@extends('layouts.admin')

@section('title', 'Templates de SMS - Shotgun MOZ')

@section('content')

    {{-- ============================================================
         CABEÇALHO DA PÁGINA
         ============================================================ --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-file-earmark-text text-primary"></i> Templates de SMS
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('sms.index') }}" class="btn btn-sm btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Voltar a SMS
            </a>
            <a href="{{ route('sms.templates.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Novo Template
            </a>
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
            <form method="GET" action="{{ route('sms.templates.index') }}">
                <div class="row g-3">

                    {{-- Filtro: Busca por nome --}}
                    <div class="col-md-4">
                        <label class="form-label">Busca</label>
                        <input type="text" name="busca" class="form-control"
                               placeholder="Nome do template..."
                               value="{{ request('busca') }}">
                    </div>

                    {{-- Filtro: Tipo --}}
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="">Todos</option>
                            @foreach($tipos as $key => $label)
                                <option value="{{ $key }}" {{ request('tipo') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro: Estado --}}
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="is_active" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Ativos</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>

                    {{-- Botões --}}
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="{{ route('sms.templates.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpar
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================
         TABELA DE TEMPLATES
         ============================================================ --}}
    <div class="card">
        <div class="card-body">

            @if($templates->count() > 0)

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th style="width: 130px;">Tipo</th>
                                <th>Mensagem</th>
                                <th class="text-center" style="width: 130px;">Estado</th>
                                <th style="width: 180px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    {{-- Nome + Assunto --}}
                                    <td>
                                        <strong>{{ $template->nome }}</strong>
                                        @if($template->assunto)
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-tag"></i> {{ $template->assunto }}
                                            </small>
                                        @endif
                                    </td>

                                    {{-- Tipo (badge) --}}
                                    <td>
                                        <span class="badge bg-{{ $template->tipo_color }}">
                                            {{ $template->tipo_label }}
                                        </span>
                                    </td>

                                    {{-- Mensagem (truncada) --}}
                                    <td>
                                        {{ Str::limit($template->mensagem, 70) }}
                                    </td>

                                    {{-- Estado (botão toggle) --}}
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm {{ $template->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                onclick="toggleAtivo({{ $template->id }}, this)"
                                                title="Clique para {{ $template->is_active ? 'desativar' : 'ativar' }}">
                                            <i class="bi bi-{{ $template->is_active ? 'check-circle' : 'x-circle' }}"></i>
                                            {{ $template->is_active ? 'Ativo' : 'Inativo' }}
                                        </button>
                                    </td>

                                    {{-- Ações --}}
                                    <td>
                                        <a href="{{ route('sms.templates.show', $template->id) }}"
                                           class="btn btn-sm btn-info" title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('sms.templates.edit', $template->id) }}"
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="confirmarExclusao({{ $template->id }}, '{{ addslashes($template->nome) }}')"
                                                title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        {{-- Form escondido para exclusão --}}
                                        <form id="delete-form-{{ $template->id }}"
                                              action="{{ route('sms.templates.destroy', $template->id) }}"
                                              method="POST"
                                              class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginação --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $templates->appends(request()->query())->links() }}
                </div>

            @else

                {{-- Estado vazio --}}
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-3">Nenhum template encontrado.</p>
                    <a href="{{ route('sms.templates.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Criar primeiro template
                    </a>
                </div>

            @endif

        </div>
    </div>

@endsection

{{-- ============================================================
     SCRIPTS
     ============================================================ --}}
@push('scripts')
<script>
    /**
     * Alterna o estado ativo/inativo do template via AJAX
     *
     * @param {number} id - ID do template
     */
    function toggleAtivo(id, btn) {
        fetch('{{ url("admin/sms/templates") }}/' + id + '/toggle-ativo', {
            method: 'POST',
            headers: {
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
                    title: 'Atualizado!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            } else {
                throw new Error(data.message || 'Erro ao atualizar.');
            }
        })
        .catch(err => {
            console.error('Erro toggle:', err);
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: err.message || 'Não foi possível atualizar o estado.'
            });
        });
    }

    /**
     * Confirma a exclusão do template
     *
     * @param {number} id - ID do template
     * @param {string} nome - Nome para mostrar no diálogo
     */
    function confirmarExclusao(id, nome) {
        Swal.fire({
            title: 'Tem certeza?',
            html: `Deseja realmente excluir o template <strong>"${nome}"</strong>?<br><small class="text-muted">Esta ação não pode ser desfeita.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush