{{--
    ============================================================
    VIEW: Detalhes do Usuário
    ============================================================
    Exibe todas as informações de um usuário e seus ingressos.
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Detalhes do Usuário - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person text-primary"></i> Detalhes do Usuário</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning ms-2">
            <i class="bi bi-pencil"></i> Editar
        </a>
    </div>
</div>

<div class="row">
    {{-- ============================================================
    COLUDA ESQUERDA - INFORMAÇÕES DO USUÁRIO
    ============================================================ --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Dados do Usuário</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="user-avatar" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto; background: #1a73e8; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        {{ $user->name[0] ?? 'U' }}
                    </div>
                </div>

                {{-- Nome --}}
                <div class="mb-2">
                    <strong>Nome:</strong> {{ $user->name }}
                </div>

                {{-- Email --}}
                <div class="mb-2">
                    <strong>Email:</strong> {{ $user->email }}
                </div>

                {{-- Telefone --}}
                <div class="mb-2">
                    <strong>Telefone:</strong> {{ $user->telefone ?? 'N/A' }}
                </div>

                {{-- NUIT --}}
                <div class="mb-2">
                    <strong>NUIT:</strong> {{ $user->nuit ?? 'N/A' }}
                </div>

                {{-- ============================================================
                PAPEL (ROLE) - CORRIGIDO
                ============================================================ --}}
                <div class="mb-2">
                    <strong>Papel:</strong>
                    @if(isset($user->role))
                        @if($user->role == 'admin')
                            <span class="badge bg-danger">Administrador</span>
                        @elseif($user->role == 'operador')
                            <span class="badge bg-info">Operador</span>
                        @elseif($user->role == 'promoter')
                            <span class="badge bg-warning text-dark">Promoter</span>
                        @elseif($user->role == 'cliente')
                            <span class="badge bg-secondary">Cliente</span>
                        @else
                            <span class="badge bg-secondary">{{ $user->role }}</span>
                        @endif
                    @else
                        <span class="badge bg-secondary">Não definido</span>
                    @endif
                </div>

                {{-- Data de Criação --}}
                <div class="mb-2">
                    <strong>Data de Criação:</strong> {{ $user->created_at->format('d/m/Y H:i') }}
                </div>

                {{-- Última Atualização --}}
                <div class="mb-2">
                    <strong>Última Atualização:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}
                </div>

                @if($user->id === auth()->id())
                    <div class="alert alert-success mt-2">
                        <i class="bi bi-check-circle"></i> Este é o seu usuário
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================
    COLUNA DIREITA - CARTEIRA DE INGRESSOS
    ============================================================ --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Carteira de Ingressos</h5>
            </div>
            <div class="card-body">
                {{-- Estatísticas --}}
                <div class="row mb-4">
                    <div class="col-3">
                        <div class="text-center">
                            <div class="number">{{ $totalIngressos ?? 0 }}</div>
                            <div class="label">Total</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center">
                            <div class="number text-success">{{ $ativos ?? 0 }}</div>
                            <div class="label text-success">Ativos</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center">
                            <div class="number text-secondary">{{ $utilizados ?? 0 }}</div>
                            <div class="label text-secondary">Utilizados</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center">
                            <div class="number text-warning">{{ $transferidos ?? 0 }}</div>
                            <div class="label text-warning">Transferidos</div>
                        </div>
                    </div>
                </div>

                {{-- Lista de Ingressos --}}
                @if(isset($user->carteira) && $user->carteira->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Código</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->carteira as $item)
                                <tr>
                                    <td>{{ $item->ingresso->evento->nome ?? 'N/A' }}</td>
                                    <td><code>{{ $item->codigo_acesso ?? $item->ingresso->qr_code ?? 'N/A' }}</code></td>
                                    <td>
                                        @if($item->status == 'ativo')
                                            <span class="badge bg-success">Ativo</span>
                                        @elseif($item->status == 'utilizado')
                                            <span class="badge bg-secondary">Utilizado</span>
                                        @elseif($item->status == 'transferido')
                                            <span class="badge bg-warning text-dark">Transferido</span>
                                        @elseif($item->status == 'reembolsado')
                                            <span class="badge bg-danger">Reembolsado</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $item->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-wallet2 text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">Usuário não possui ingressos na carteira.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection