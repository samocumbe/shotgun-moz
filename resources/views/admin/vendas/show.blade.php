{{--
    ============================================================
    VIEW: Detalhes da Venda
    ============================================================
    Exibe todas as informações de uma venda, incluindo:
    - Dados do cliente
    - Ingressos comprados
    - Pagamento
    - Status
    - Ações (Baixar PDF, Ver QR Codes)
    
    NOTA: O botão "Enviar Email" está visível mas com
    funcionalidade desativada (aguardando implementação futura)
    ============================================================
--}}

@extends('layouts.admin')

@section('title', 'Detalhes da Venda - Shotgun MOZ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes da Venda #{{ $venda->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        {{-- Botão Voltar para lista de vendas --}}
        <a href="{{ route('vendas.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        
        {{-- Botão Cancelar Venda (apenas se não estiver cancelada) --}}
        @if($venda->status != 'cancelada')
        <button onclick="confirmCancel({{ $venda->id }})" class="btn btn-sm btn-danger ms-2">
            <i class="bi bi-x-lg"></i> Cancelar Venda
        </button>
        {{-- Formulário oculto para cancelamento --}}
        <form id="cancel-form-{{ $venda->id }}" 
              action="{{ route('vendas.cancelar', $venda->id) }}" 
              method="POST" 
              style="display: none;">
            @csrf
        </form>
        @endif
    </div>
</div>

<div class="row">
    {{-- ============================================================
    COLUNA PRINCIPAL (8 colunas)
    ============================================================ --}}
    <div class="col-md-8">
        
        {{-- ============================================================
        CARD: INFORMAÇÕES DA VENDA
        ============================================================ --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações da Venda</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Número:</strong> #{{ $venda->id }}
                    </div>
                    <div class="col-md-6">
                        <strong>Data:</strong> {{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Evento:</strong> {{ $venda->evento->nome ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Tipo:</strong>
                        @if($venda->tipo == 'presencial')
                            <span class="badge bg-primary">Presencial</span>
                        @elseif($venda->tipo == 'online')
                            <span class="badge bg-info">Online</span>
                        @elseif($venda->tipo == 'promoter')
                            <span class="badge bg-warning text-dark">Promoter</span>
                        @endif
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Valor Total:</strong> 
                        <h4 class="text-success">{{ number_format($venda->valor_total, 2, ',', '.') }} MZN</h4>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        @if($venda->status == 'confirmada')
                            <span class="badge badge-status badge-published">
                                <i class="bi bi-check-circle"></i> Confirmada
                            </span>
                        @elseif($venda->status == 'pendente')
                            <span class="badge badge-status badge-draft">
                                <i class="bi bi-clock"></i> Pendente
                            </span>
                        @elseif($venda->status == 'cancelada')
                            <span class="badge badge-status badge-canceled">
                                <i class="bi bi-x-circle"></i> Cancelada
                            </span>
                        @endif
                    </div>
                </div>
                @if($venda->observacoes)
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Observações:</strong> {{ $venda->observacoes }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        {{-- ============================================================
        CARD: CLIENTE
        ============================================================ --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nome:</strong> {{ $venda->participante->nome_completo ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong> {{ $venda->participante->email ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Telefone:</strong> {{ $venda->participante->telefone ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Documento:</strong> {{ $venda->participante->cpf ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        
        {{-- ============================================================
        CARD: INGRESSOS
        ============================================================ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-ticket"></i> Ingressos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>QR Code</th>
                                <th>Categoria</th>
                                <th>Preço</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($venda->pedido->itens ?? [] as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <code>{{ $item->ingresso->qr_code ?? 'N/A' }}</code>
                                    @if($item->ingresso)
                                        {{-- CORRIGIDO: Passando ambos os parâmetros necessários --}}
                                        <a href="{{ route('ingressos.qrcode', ['eventoId' => $item->ingresso->evento_id, 'id' => $item->ingresso->id]) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-info ms-1" 
                                           title="Ver QR Code">
                                            <i class="bi bi-qr-code"></i>
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $item->categoria->nome ?? 'N/A' }}</td>
                                <td>{{ number_format($item->preco_unitario, 2, ',', '.') }} MZN</td>
                                <td>
                                    @if($item->ingresso)
                                        @if($item->ingresso->status == 'pago')
                                            <span class="badge bg-success">Pago</span>
                                        @elseif($item->ingresso->status == 'utilizado')
                                            <span class="badge bg-info">Utilizado</span>
                                        @elseif($item->ingresso->status == 'cancelado')
                                            <span class="badge bg-danger">Cancelado</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $item->ingresso->status }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Pendente</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Nenhum ingresso encontrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td colspan="2"><strong>{{ number_format($venda->valor_total, 2, ',', '.') }} MZN</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- ============================================================
    SIDEBAR (4 colunas)
    ============================================================ --}}
    <div class="col-md-4">
        
        {{-- ============================================================
        CARD: PAGAMENTO
        ============================================================ --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Pagamento</h5>
            </div>
            <div class="card-body">
                @if($venda->pedido && $venda->pedido->pagamentos && $venda->pedido->pagamentos->count() > 0)
                    @foreach($venda->pedido->pagamentos as $pagamento)
                    <div class="mb-2">
                        <strong>Método:</strong> 
                        @if($pagamento->metodo == 'mpesa')
                            <span class="badge bg-success">M-Pesa</span>
                        @elseif($pagamento->metodo == 'emola')
                            <span class="badge bg-info">e-Mola</span>
                        @elseif($pagamento->metodo == 'dinheiro')
                            <span class="badge bg-warning text-dark">Dinheiro</span>
                        @elseif($pagamento->metodo == 'cartao')
                            <span class="badge bg-primary">Cartão</span>
                        @else
                            {{ ucfirst($pagamento->metodo) }}
                        @endif
                    </div>
                    <div>
                        <strong>Status:</strong>
                        @if($pagamento->status == 'confirmado')
                            <span class="badge bg-success">Confirmado</span>
                        @elseif($pagamento->status == 'pendente')
                            <span class="badge bg-warning text-dark">Pendente</span>
                        @elseif($pagamento->status == 'falha')
                            <span class="badge bg-danger">Falha</span>
                        @else
                            {{ ucfirst($pagamento->status) }}
                        @endif
                    </div>
                    @if($pagamento->valor)
                    <div class="mt-2">
                        <strong>Valor:</strong> {{ number_format($pagamento->valor, 2, ',', '.') }} MZN
                    </div>
                    @endif
                    @if($pagamento->referencia)
                    <div class="mt-2">
                        <strong>Referência:</strong> {{ $pagamento->referencia }}
                    </div>
                    @endif
                    @if($pagamento->codigo_confirmacao)
                    <div class="mt-2">
                        <strong>Código:</strong> {{ $pagamento->codigo_confirmacao }}
                    </div>
                    @endif
                    <div class="mt-2">
                        <strong>Data:</strong> {{ \Carbon\Carbon::parse($pagamento->created_at)->format('d/m/Y H:i') }}
                    </div>
                    @endforeach
                @else
                    <p class="text-muted">Nenhum pagamento registrado</p>
                @endif
            </div>
        </div>
        
        {{-- ============================================================
        CARD: AÇÕES
        ============================================================ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Ações</h5>
            </div>
            <div class="card-body">
                {{-- Botão Baixar PDF (funcional) --}}
                <a href="{{ route('vendas.recibo', $venda->id) }}" 
                   target="_blank" 
                   class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-file-pdf"></i> Baixar PDF
                </a>
                
                {{-- Botão Enviar Email (desativado - implementação futura) --}}
                <button onclick="enviarEmailFuturo({{ $venda->id }})" 
                        class="btn btn-secondary w-100 mb-2" 
                        title="Funcionalidade em desenvolvimento">
                    <i class="bi bi-envelope"></i> Enviar Email <span class="badge bg-warning text-dark ms-1">Em breve</span>
                </button>
                
                {{-- Botão Ver QR Codes (funcional) --}}
                <button onclick="verQRCodes({{ $venda->id }})" class="btn btn-info w-100">
                    <i class="bi bi-qr-code"></i> Ver QR Codes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ============================================================
SCRIPTS
============================================================ --}}
@push('scripts')
<script>
    /**
     * ============================================================
     * CONFIRMAR CANCELAMENTO
     * ============================================================
     * Exibe um modal SweetAlert2 para confirmar o cancelamento
     * da venda. Se confirmado, submete o formulário oculto.
     * 
     * @param int id - ID da venda
     */
    function confirmCancel(id) {
        Swal.fire({
            title: 'Cancelar venda?',
            text: 'Esta ação não pode ser desfeita. O cliente será reembolsado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="bi bi-x-lg"></i> Sim, cancelar!',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Não'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancel-form-' + id).submit();
            }
        });
    }

    /**
     * ============================================================
     * ENVIAR EMAIL (FUTURO)
     * ============================================================
     * Esta função é um placeholder para quando o envio de email
     * for implementado. Atualmente exibe uma mensagem informativa.
     * 
     * @param int vendaId - ID da venda
     */
    function enviarEmailFuturo(vendaId) {
        Swal.fire({
            icon: 'info',
            title: 'Funcionalidade em desenvolvimento',
            text: 'O envio de comprovante por email estará disponível em breve.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6'
        });
    }

    /**
     * ============================================================
     * VER QR CODES
     * ============================================================
     * Busca os ingressos da venda via AJAX e exibe os QR Codes
     * em um modal SweetAlert2.
     * 
     * @param int vendaId - ID da venda
     */
    function verQRCodes(vendaId) {
        // Mostrar loading
        Swal.fire({
            title: 'Carregando...',
            text: 'Aguarde enquanto carregamos os QR Codes.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Buscar os ingressos da venda via AJAX
        fetch('/admin/vendas/' + vendaId + '/ingressos')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Erro ao carregar ingressos');
            }

            // Construir o HTML com os QR Codes
            let html = '<div style="max-height: 500px; overflow-y: auto; padding: 10px;">';
            
            if (data.ingressos && data.ingressos.length > 0) {
                data.ingressos.forEach((ingresso, index) => {
                    // Usar o QR Code em base64 (gerado pelo backend)
                    let qrImage = ingresso.qr_code_base64 
                        ? `<img src="data:image/png;base64,${ingresso.qr_code_base64}" alt="QR Code" style="max-width: 120px; height: auto;" />`
                        : `<img src="${ingresso.qr_code_url}" alt="QR Code" style="max-width: 120px; height: auto;" />`;
                    
                    html += `
                        <div class="mb-3 p-3 border rounded" style="background: #f8f9fa;">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <p class="mb-0"><strong>Ingresso #${index + 1}</strong></p>
                                    <p class="mb-0"><small>Código: <code>${ingresso.qr_code}</code></small></p>
                                    <p class="mb-0"><small>Categoria: ${ingresso.categoria}</small></p>
                                    <p class="mb-0"><small>Preço: ${ingresso.preco || 'N/A'}</small></p>
                                    <p class="mb-0">
                                        <span class="badge ${ingresso.status === 'pago' ? 'bg-success' : ingresso.status === 'utilizado' ? 'bg-info' : ingresso.status === 'cancelado' ? 'bg-danger' : 'bg-warning'}">
                                            ${ingresso.status || 'N/A'}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-4 text-center">
                                    ${qrImage}
                                    <br>
                                    <small>
                                        <a href="${ingresso.qr_code_url}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                            <i class="bi bi-box-arrow-up-right"></i> Abrir
                                        </a>
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html += '<p class="text-center text-muted">Nenhum ingresso encontrado para esta venda.</p>';
            }
            
            html += '</div>';

            Swal.fire({
                title: '🎫 QR Codes dos Ingressos',
                html: html,
                width: 700,
                confirmButtonText: 'Fechar',
                confirmButtonColor: '#3085d6'
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: error.message || 'Não foi possível carregar os QR Codes.'
            });
        });
    }
</script>
@endpush