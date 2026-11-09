{{--
    ============================================================
    VIEW: Dispositivos - Detalhes
    ============================================================
    Ficheiro: resources/views/admin/dispositivos/show.blade.php
--}}

@extends('layouts.admin')

@section('title', 'Dispositivo - ' . $dispositivo->nome)

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="bi bi-phone text-primary"></i>
            {{ $dispositivo->nome }}
            @if($dispositivo->status === 'ativo')
                <span class="badge bg-success">Ativo</span>
            @else
                <span class="badge bg-secondary">Inativo</span>
            @endif
        </h1>
        <div>
            <a href="{{ route('dispositivos.edit', $dispositivo->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('dispositivos.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="number text-primary">{{ $estatisticas['checkins_realizados'] }}</div>
                <div class="label">Check-ins Realizados</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="number text-warning">{{ $estatisticas['checkins_pendentes'] }}</div>
                <div class="label text-warning">Check-ins Pendentes</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="number text-info">{{ $estatisticas['operacoes_sync'] }}</div>
                <div class="label text-info">Operações de Sync</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="number text-success" style="font-size: 1.1rem;">
                    @if($estatisticas['ultimo_sync'])
                        {{ $estatisticas['ultimo_sync']->format('d/m/Y H:i') }}
                    @else
                        —
                    @endif
                </div>
                <div class="label">Último Sync</div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Informações gerais --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th style="width: 180px;">ID</th>
                            <td>#{{ $dispositivo->id }}</td>
                        </tr>
                        <tr>
                            <th>UUID</th>
                            <td><code>{{ $dispositivo->uuid }}</code></td>
                        </tr>
                        <tr>
                            <th>Nome</th>
                            <td>{{ $dispositivo->nome }}</td>
                        </tr>
                        <tr>
                            <th>Tipo</th>
                            <td>
                                @switch($dispositivo->tipo)
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
                        </tr>
                        <tr>
                            <th>Evento</th>
                            <td>
                                @if($dispositivo->evento)
                                    <a href="{{ route('eventos.show', $dispositivo->evento->id) }}">
                                        {{ $dispositivo->evento->nome }}
                                    </a>
                                @else
                                    <span class="text-muted">Sem evento associado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Utilizador</th>
                            <td>{{ $dispositivo->user->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td>
                                @if($dispositivo->status === 'ativo')
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Criado em</th>
                            <td>{{ $dispositivo->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Atualizado em</th>
                            <td>{{ $dispositivo->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Ações rápidas --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Ações Rápidas</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    {{-- Sincronizar --}}
                    <form action="{{ route('dispositivos.sincronizar', $dispositivo->id) }}"
                          method="POST"
                          onsubmit="return confirm('Sincronizar este dispositivo agora?');">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-arrow-repeat"></i> Sincronizar Agora
                        </button>
                    </form>

                    {{-- Editar --}}
                    <a href="{{ route('dispositivos.edit', $dispositivo->id) }}"
                       class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>

                    {{-- Eliminar --}}
                    <form action="{{ route('dispositivos.destroy', $dispositivo->id) }}"
                          method="POST" id="delete-form-{{ $dispositivo->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger w-100"
                                onclick="confirmDelete({{ $dispositivo->id }}, '{{ addslashes($dispositivo->nome) }}')">
                            <i class="bi bi-trash"></i> Eliminar Dispositivo
                        </button>
                    </form>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <small>
                    <i class="bi bi-info-circle"></i>
                    O dispositivo usa este <strong>UUID</strong> para autenticar na API:
                    <br>
                    <code style="word-break: break-all; font-size: 0.75rem;">
                        {{ $dispositivo->uuid }}
                    </code>
                </small>
            </div>
        </div>
    </div>

@endsection