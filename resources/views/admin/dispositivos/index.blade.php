{{--
    ============================================================
    VIEW: Dispositivos - Listagem
    ============================================================
    Ficheiro: resources/views/admin/dispositivos/index.blade.php
--}}

@extends('layouts.admin')

@section('title', 'Dispositivos - Shotgun MOZ')

@section('content')

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-phone text-primary"></i> Dispositivos
        </h1>
        <a href="{{ route('dispositivos.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Novo Dispositivo
        </a>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dispositivos.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control"
                               value="{{ request('nome') }}" placeholder="Pesquisar...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="">Todos</option>
                            <option value="scanner"    {{ request('tipo') == 'scanner'    ? 'selected' : '' }}>Scanner</option>
                            <option value="pos"        {{ request('tipo') == 'pos'        ? 'selected' : '' }}>POS</option>
                            <option value="supervisor" {{ request('tipo') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="admin"      {{ request('tipo') == 'admin'      ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="ativo"   {{ request('status') == 'ativo'   ? 'selected' : '' }}>Ativo</option>
                            <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Evento</label>
                        <select name="evento_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($eventos as $ev)
                                <option value="{{ $ev->id }}" {{ request('evento_id') == $ev->id ? 'selected' : '' }}>
                                    {{ $ev->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="{{ route('dispositivos.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card">
        <div class="card-body">
            @if($dispositivos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Evento</th>
                                <th>Utilizador</th>
                                <th>Último Sync</th>
                                <th>Status</th>
                                <th style="width: 180px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dispositivos as $d)
                                <tr>
                                    <td><span class="text-muted">#{{ $d->id }}</span></td>
                                    <td>
                                        <strong>{{ $d->nome }}</strong>
                                        <br>
                                        <small class="text-muted">UUID: {{ \Str::limit($d->uuid, 18) }}</small>
                                    </td>
                                    <td>
                                        @switch($d->tipo)
                                            @case('scanner')
                                                <span class="badge bg-primary"><i class="bi bi-qr-code-scan"></i> Scanner</span>
                                                @break
                                            @case('pos')
                                                <span class="badge bg-success"><i class="bi bi-cash-register"></i> POS</span>
                                                @break
                                            @case('supervisor')
                                                <span class="badge bg-warning text-dark"><i class="bi bi-eye"></i> Supervisor</span>
                                                @break
                                            @case('admin')
                                                <span class="badge bg-danger"><i class="bi bi-shield-check"></i> Admin</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($d->evento)
                                            <a href="{{ route('eventos.show', $d->evento->id) }}">
                                                {{ \Str::limit($d->evento->nome, 25) }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $d->user->name ?? '—' }}</td>
                                    <td>
                                        @if($d->ultimo_sync)
                                            <small>{{ $d->ultimo_sync->format('d/m/Y H:i') }}</small>
                                            <br>
                                            <small class="text-muted">{{ $d->ultimo_sync->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">Nunca</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($d->status === 'ativo')
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Ver --}}
                                        <a href="{{ route('dispositivos.show', $d->id) }}"
                                           class="btn btn-sm btn-info" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        {{-- Editar --}}
                                        <a href="{{ route('dispositivos.edit', $d->id) }}"
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        {{-- Sincronizar --}}
                                        <form action="{{ route('dispositivos.sincronizar', $d->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Sincronizar este dispositivo?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="Sincronizar">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>
                                        {{-- Eliminar --}}
                                        <form action="{{ route('dispositivos.destroy', $d->id) }}"
                                              method="POST" class="d-inline"
                                              id="delete-form-{{ $d->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                                    onclick="confirmDelete({{ $d->id }}, '{{ addslashes($d->nome) }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $dispositivos->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-phone fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-3">Nenhum dispositivo registado.</p>
                    <a href="{{ route('dispositivos.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Criar primeiro dispositivo
                    </a>
                </div>
            @endif
        </div>
    </div>

@endsection