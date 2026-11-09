{{--
    ============================================================
    VIEW: Lista de Notificações
    ============================================================
    Exibe todas as notificações do usuário com opções de:
    - Marcar como lida
    - Marcar todas como lidas
    - Remover notificação
--}}

@extends('layouts.admin')

@section('title', 'Notificações - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Notificações</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('notificacoes.marcar-todas-lidas') }}" 
           class="btn btn-sm btn-success"
           onclick="event.preventDefault(); document.getElementById('marcar-todas-form').submit();">
            <i class="bi bi-check-all"></i> Marcar todas como lidas
        </a>
        <form id="marcar-todas-form" action="{{ route('notificacoes.marcar-todas-lidas') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @forelse($notificacoes as $notificacao)
        <div class="notification-item p-3 mb-2 rounded {{ $notificacao->lida ? 'bg-light' : 'bg-primary bg-opacity-10 border-start border-primary border-4' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-1">
                        @if(!$notificacao->lida)
                            <span class="badge bg-primary me-2">Nova</span>
                        @endif
                        <h5 class="mb-0">{{ $notificacao->titulo }}</h5>
                    </div>
                    <p class="mb-1">{{ $notificacao->mensagem }}</p>
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> 
                        {{ \Carbon\Carbon::parse($notificacao->created_at)->diffForHumans() }}
                        @if($notificacao->lida && $notificacao->data_leitura)
                            <span class="ms-2">
                                <i class="bi bi-check-circle text-success"></i> 
                                Lida em {{ \Carbon\Carbon::parse($notificacao->data_leitura)->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </small>
                </div>
                <div class="d-flex gap-2">
                    @if(!$notificacao->lida)
                        <form action="{{ route('notificacoes.marcar-lida', $notificacao->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary" title="Marcar como lida">
                                <i class="bi bi-check"></i>
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('notificacoes.destroy', $notificacao->id) }}" method="POST" 
                          onsubmit="return confirm('Remover esta notificação?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
            <p class="mt-3">Nenhuma notificação encontrada.</p>
        </div>
        @endforelse

        {{ $notificacoes->links() }}
    </div>
</div>
@endsection