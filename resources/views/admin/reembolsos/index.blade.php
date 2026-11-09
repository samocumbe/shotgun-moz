@extends('layouts.admin')

@section('title', 'Meus Reembolsos - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-arrow-return-left text-danger"></i> Meus Reembolsos</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ingresso</th>
                        <th>Valor</th>
                        <th>Motivo</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reembolsos as $reembolso)
                    <tr>
                        <td>{{ $reembolso->id }}</td>
                        <td>{{ $reembolso->carteiraIngresso->ingresso->evento->nome ?? 'N/A' }}</td>
                        <td><strong>{{ number_format($reembolso->valor_reembolso, 2, ',', '.') }} MZN</strong></td>
                        <td>{{ $reembolso->motivo }}</td>
                        <td>
                            @if($reembolso->status == 'pendente')
                                <span class="badge badge-status badge-draft">Pendente</span>
                            @elseif($reembolso->status == 'aprovado')
                                <span class="badge badge-status badge-published">Aprovado</span>
                            @elseif($reembolso->status == 'rejeitado')
                                <span class="badge badge-status badge-canceled">Rejeitado</span>
                            @elseif($reembolso->status == 'processado')
                                <span class="badge badge-status badge-ended">Processado</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($reembolso->created_at)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('reembolsos.show', $reembolso->id) }}" 
                               class="btn btn-sm btn-info" title="Ver detalhes">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-arrow-return-left" style="font-size: 2rem;"></i>
                            <p class="mt-2">Nenhuma solicitação de reembolso encontrada.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $reembolsos->links() }}
    </div>
</div>
@endsection