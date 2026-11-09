{{--
    ============================================================
    VIEW: Lista de Pagamentos
    ============================================================
    Exibe todos os pagamentos com filtros.
--}}

@extends('layouts.admin')

@section('title', 'Pagamentos - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Pagamentos</h1>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('pagamentos.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="metodo" class="form-label">Método</label>
                <select class="form-select" id="metodo" name="metodo">
                    <option value="">Todos</option>
                    <option value="mpesa" {{ request('metodo') == 'mpesa' ? 'selected' : '' }}>📱 M-Pesa</option>
                    <option value="emola" {{ request('metodo') == 'emola' ? 'selected' : '' }}>📱 e-Mola</option>
                    <option value="dinheiro" {{ request('metodo') == 'dinheiro' ? 'selected' : '' }}>💰 Dinheiro</option>
                    <option value="cartao" {{ request('metodo') == 'cartao' ? 'selected' : '' }}>💳 Cartão</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="confirmado" {{ request('status') == 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="falha" {{ request('status') == 'falha' ? 'selected' : '' }}>Falha</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                       value="{{ request('data_inicio') }}">
            </div>
            <div class="col-md-2">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" 
                       value="{{ request('data_fim') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Pagamentos --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pedido</th>
                        <th>Valor</th>
                        <th>Método</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagamentos as $pagamento)
                    <tr>
                        <td>#{{ $pagamento->id }}</td>
                        <td>
                            <a href="{{ route('pedidos.show', $pagamento->pedido_id) }}" class="text-primary">
                                {{ $pagamento->pedido->numero_pedido ?? 'N/A' }}
                            </a>
                        </td>
                        <td><strong>{{ number_format($pagamento->valor, 2, ',', '.') }} MZN</strong></td>
                        <td>
                            @if($pagamento->metodo == 'mpesa')
                                <span class="badge bg-success">📱 M-Pesa</span>
                            @elseif($pagamento->metodo == 'emola')
                                <span class="badge bg-info">📱 e-Mola</span>
                            @elseif($pagamento->metodo == 'dinheiro')
                                <span class="badge bg-warning text-dark">💰 Dinheiro</span>
                            @elseif($pagamento->metodo == 'cartao')
                                <span class="badge bg-primary">💳 Cartão</span>
                            @endif
                        </td>
                        <td>
                            @if($pagamento->status == 'confirmado')
                                <span class="badge badge-status badge-published">Confirmado</span>
                            @elseif($pagamento->status == 'pendente')
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @elseif($pagamento->status == 'falha')
                                <span class="badge badge-status badge-canceled">Falha</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($pagamento->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('pagamentos.show', $pagamento->id) }}" 
                               class="btn btn-sm btn-info" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($pagamento->status == 'pendente')
                                <form action="{{ route('pagamentos.confirmar', $pagamento->id) }}" 
                                      method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Confirmar">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-credit-card" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhum pagamento registrado.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Paginação --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Mostrando {{ $pagamentos->firstItem() ?? 0 }} até {{ $pagamentos->lastItem() ?? 0 }} 
                    de {{ $pagamentos->total() }} registros
                </small>
            </div>
            <div>
                {{ $pagamentos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection