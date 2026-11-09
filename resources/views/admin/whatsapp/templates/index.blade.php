{{--
    ============================================================
    VIEW: Lista de Templates WhatsApp - SHOTGUN-MOZ
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Templates WhatsApp - Shotgun MOZ')

@section('content')

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-file-earmark-text text-success"></i> Templates WhatsApp
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('whatsapp.templates.create') }}" class="btn btn-sm btn-success me-2">
                <i class="bi bi-plus"></i> Novo Template
            </a>
            <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="number text-primary">{{ $estatisticas['total'] ?? 0 }}</div>
                <div class="label">Total</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="number text-success">{{ $estatisticas['ativos'] ?? 0 }}</div>
                <div class="label text-success">Ativos</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="number text-info">{{ $estatisticas['aprovados'] ?? 0 }}</div>
                <div class="label text-info">Aprovados</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="number text-warning">{{ $estatisticas['pendentes'] ?? 0 }}</div>
                <div class="label text-warning">Pendentes</div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('whatsapp.templates.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Nome ou código..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Categoria</label>
                        <select name="categoria" class="form-select">
                            <option value="">Todas</option>
                            <option value="marketing"    {{ request('categoria') == 'marketing'    ? 'selected' : '' }}>Marketing</option>
                            <option value="utilidade"    {{ request('categoria') == 'utilidade'    ? 'selected' : '' }}>Utilidade</option>
                            <option value="autenticacao" {{ request('categoria') == 'autenticacao' ? 'selected' : '' }}>Autenticação</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendente"  {{ request('status') == 'pendente'  ? 'selected' : '' }}>Pendente</option>
                            <option value="aprovado"  {{ request('status') == 'aprovado'  ? 'selected' : '' }}>Aprovado</option>
                            <option value="rejeitado" {{ request('status') == 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="is_active" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Ativos</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card">
        <div class="card-body">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Nome</th>
                                <th style="width: 160px;">Código</th>
                                <th style="width: 120px;">Categoria</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 90px;">Ativo</th>
                                <th style="width: 170px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $tpl)
                                <tr>
                                    <td><span class="text-muted">#{{ $tpl->id }}</span></td>
                                    <td>
                                        <strong>{{ $tpl->nome }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($tpl->mensagem, 50) }}</small>
                                    </td>
                                    <td><code>{{ $tpl->codigo }}</code></td>
                                    <td>
                                        @switch($tpl->categoria)
                                            @case('marketing')
                                                <span class="badge bg-primary">Marketing</span>
                                                @break
                                            @case('utilidade')
                                                <span class="badge bg-info text-dark">Utilidade</span>
                                                @break
                                            @case('autenticacao')
                                                <span class="badge bg-secondary">Autenticação</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @switch($tpl->status)
                                            @case('aprovado')
                                                <span class="badge bg-success">Aprovado</span>
                                                @break
                                            @case('pendente')
                                                <span class="badge bg-warning text-dark">Pendente</span>
                                                @break
                                            @case('rejeitado')
                                                <span class="badge bg-danger">Rejeitado</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   role="switch"
                                                   id="toggle-{{ $tpl->id }}"
                                                   {{ $tpl->is_active ? 'checked' : '' }}
                                                   onchange="toggleAtivo({{ $tpl->id }})">
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('whatsapp.templates.show', $tpl->id) }}"
                                           class="btn btn-sm btn-info" title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('whatsapp.templates.edit', $tpl->id) }}"
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                onclick="previewTemplate({{ $tpl->id }})"
                                                class="btn btn-sm btn-secondary"
                                                title="Pré-visualizar">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <button type="button"
                                                onclick="confirmDelete({{ $tpl->id }}, '{{ $tpl->nome }}')"
                                                class="btn btn-sm btn-danger"
                                                title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $tpl->id }}"
                                              action="{{ route('whatsapp.templates.destroy', $tpl->id) }}"
                                              method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $templates->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-3">Nenhum template encontrado.</p>
                    <a href="{{ route('whatsapp.templates.create') }}" class="btn btn-success">
                        <i class="bi bi-plus"></i> Criar primeiro template
                    </a>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
{{-- ⚠️ Usamos @verbatim para que o Blade não interprete
     as template literals (`` ` ``) do JavaScript.
     Sem isso, ${...} e { } colidem com a sintaxe Blade. --}}
@verbatim
<script>
    // ============================================================
    // TOGGLE ATIVO/INATIVO
    // ============================================================
    function toggleAtivo(id) {
        fetch('/admin/whatsapp/templates/' + id + '/toggle-ativo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                throw new Error(data.message || 'Erro ao alterar estado.');
            }
        })
        .catch(function (err) {
            Swal.fire({ icon: 'error', title: 'Erro!', text: err.message });
            var input = document.getElementById('toggle-' + id);
            if (input) input.checked = !input.checked;
        });
    }

    // ============================================================
    // PRÉ-VISUALIZAR TEMPLATE
    // ============================================================
    function previewTemplate(id) {
        fetch('/admin/whatsapp/templates/' + id + '/preview-json', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            // Construir HTML das variáveis (sem template literals)
            var varsHtml = '';
            if (data.variaveis && data.variaveis.length > 0) {
                varsHtml = '<p class="mb-2"><strong>Variáveis:</strong> ';
                for (var i = 0; i < data.variaveis.length; i++) {
                    varsHtml += '<code>{{' + data.variaveis[i] + '}}</code> ';
                }
                varsHtml += '</p>';
            } else {
                varsHtml = '<p class="mb-2 text-muted"><em>Sem variáveis</em></p>';
            }

            // Mensagem renderizada (quebrar linhas)
            var mensagemHtml = data.renderizado
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');

            // Montar HTML final (concatenação, sem template literals)
            var htmlFinal = ''
                + '<div style="text-align:left;">'
                +   varsHtml
                +   '<p class="mb-0"><strong>Mensagem:</strong></p>'
                +   '<div style="background:#e7ffdb;padding:12px;border-radius:8px;margin-top:8px;white-space:pre-wrap;text-align:left;">'
                +     mensagemHtml
                +   '</div>'
                + '</div>';

            Swal.fire({
                title: 'Pré-visualização',
                html: htmlFinal,
                icon: 'info',
                confirmButtonText: 'Fechar',
                confirmButtonColor: '#25D366',
                width: 600
            });
        })
        .catch(function () {
            Swal.fire({ icon: 'error', title: 'Erro!', text: 'Não foi possível pré-visualizar.' });
        });
    }
</script>
@endverbatim
@endpush