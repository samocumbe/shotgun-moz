@extends('layouts.admin')

@section('title', 'Escolher Clientes - WhatsApp em Massa')

@section('content')

    {{-- ========================================================
         CABEÇALHO DA PÁGINA
         ======================================================== --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-people text-success"></i>
            Escolher Clientes
            <small class="text-muted fs-6">— {{ $evento->nome }}</small>
        </h1>
        <a href="{{ route('whatsapp.massa.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('whatsapp.massa.preview') }}" id="form-massa">
        @csrf
        <input type="hidden" name="evento_id" value="{{ $evento->id }}">

        <div class="row">

            {{-- ========================================================
                 COLUNA ESQUERDA: LISTA DE CLIENTES
                 ======================================================== --}}
            <div class="col-md-7">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-person-lines-fill"></i>
                            Clientes ({{ count($clientes) }})
                        </h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="selecionarTodos()">
                                <i class="bi bi-check-all"></i> Todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limparSelecao()">
                                <i class="bi bi-x"></i> Nenhum
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($clientes) > 0)
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input"
                                                       id="check-all" onclick="toggleTodos(this)">
                                            </th>
                                            <th>Nome</th>
                                            <th>Telefone</th>
                                            <th>Ingressos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($clientes as $cliente)
                                            <tr>
                                                <td>
                                                    <input type="checkbox"
                                                           name="clientes[]"
                                                           value="{{ $cliente['id'] }}"
                                                           class="form-check-input cliente-check"
                                                           onchange="atualizarContador()">
                                                </td>
                                                <td>{{ $cliente['nome'] }}</td>
                                                <td><code>{{ $cliente['telefone'] }}</code></td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $cliente['total_ingressos'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2 text-muted small">
                                <i class="bi bi-info-circle"></i>
                                <strong id="contador-selecionados">0</strong> cliente(s) selecionado(s)
                            </div>
                        @else
                            <p class="text-muted text-center py-4 mb-0">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                Nenhum cliente encontrado para este evento.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ========================================================
                 COLUNA DIREITA: TEMPLATE + MENSAGEM
                 ======================================================== --}}
            <div class="col-md-5">

                {{-- ------------------ TEMPLATE (opcional) ------------------ --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Template (opcional)</h5>
                    </div>
                    <div class="card-body">
                        <select name="template_id" id="template_id" class="form-select">
                            <option value="">— Escrever manualmente —</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}" data-conteudo="{{ $tpl->mensagem }}">
                                    {{ $tpl->nome }}
                                    @if($tpl->status === 'aprovado') ✓ @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">
                            <a href="{{ route('whatsapp.templates.index') }}" target="_blank">
                                <i class="bi bi-gear"></i> Gerir templates
                            </a>
                        </small>
                    </div>
                </div>

                {{-- ------------------ MENSAGEM ------------------ --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat"></i> Mensagem</h5>
                    </div>
                    <div class="card-body">

                        <textarea name="mensagem" id="mensagem" rows="6"
                                  class="form-control" maxlength="4096"
                                  required></textarea>

                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">
                                <span id="caracteres">0</span> / 4096
                            </small>
                        </div>

                        {{-- ====================================================
                             HINT DE VARIÁVEIS
                             As chaves vêm do Controller como strings HTML
                             já renderizadas — evita problemas com o Blade.
                             ==================================================== --}}
                        <div class="alert alert-light border mt-3 mb-0 p-2">
                            <small class="d-block mb-1">
                                <strong><i class="bi bi-lightbulb"></i> Variáveis disponíveis:</strong>
                            </small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($variaveisHint as $hint)
                                    {!! $hint !!}
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-2">
                                {!! $variaveisHintInfo !!}
                            </small>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-success"
                                    {{ count($clientes) == 0 ? 'disabled' : '' }}>
                                <i class="bi bi-eye"></i> Pré-visualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    const mensagem    = document.getElementById('mensagem');
    const templateSel = document.getElementById('template_id');
    const charEl      = document.getElementById('caracteres');
    const contEl      = document.getElementById('contador-selecionados');

    function atualizarContadorCaracteres() {
        charEl.textContent = mensagem.value.length;
    }
    mensagem.addEventListener('input', atualizarContadorCaracteres);

    templateSel.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const conteudo = opt.dataset.conteudo;
        if (conteudo) {
            mensagem.value = conteudo;
            atualizarContadorCaracteres();
        }
    });

    function selecionarTodos() {
        document.querySelectorAll('.cliente-check').forEach(c => c.checked = true);
        document.getElementById('check-all').checked = true;
        atualizarContador();
    }

    function limparSelecao() {
        document.querySelectorAll('.cliente-check').forEach(c => c.checked = false);
        document.getElementById('check-all').checked = false;
        atualizarContador();
    }

    function toggleTodos(master) {
        document.querySelectorAll('.cliente-check').forEach(c => c.checked = master.checked);
        atualizarContador();
    }

    function atualizarContador() {
        contEl.textContent = document.querySelectorAll('.cliente-check:checked').length;
    }

    document.getElementById('form-massa').addEventListener('submit', function(e) {
        const total = document.querySelectorAll('.cliente-check:checked').length;
        if (total === 0) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Nenhum cliente selecionado', text: 'Seleciona pelo menos um cliente.' });
            return;
        }
        if (mensagem.value.trim().length < 5) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Mensagem vazia', text: 'Escreve uma mensagem antes de continuar.' });
        }
    });
</script>
@endpush