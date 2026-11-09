{{--
    ============================================================
    VIEW: Recibo em PDF para Impressão
    ============================================================
    Esta view é usada para gerar o PDF do recibo da venda.
    É carregada pelo DomPDF e exportada como PDF.
    
    Layout otimizado para impressão com:
    - Sem margens desnecessárias
    - Cores e estilos compatíveis com PDF
    - QR Code para validação
    - Todos os dados da venda
    - Design profissional para comprovante oficial
    ============================================================
--}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo - Venda #{{ $venda->id }}</title>
    <style>
        /* ============================================
           RESET E CONFIGURAÇÕES BÁSICAS
           ============================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            padding: 30px;
            background: #ffffff;
            color: #333333;
            font-size: 13px;
            line-height: 1.5;
        }
        
        /* ============================================
           CONTAINER PRINCIPAL
           ============================================ */
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 30px;
            background: #ffffff;
        }
        
        /* ============================================
           HEADER - LOGO E TÍTULO
           ============================================ */
        .header {
            text-align: center;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .logo {
            font-size: 26px;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .logo small {
            font-size: 12px;
            color: #666666;
            display: block;
            font-weight: normal;
        }
        
        .title {
            font-size: 20px;
            color: #2c3e50;
            margin: 10px 0 5px 0;
            font-weight: bold;
        }
        
        .subtitle {
            font-size: 13px;
            color: #666666;
        }
        
        /* ============================================
           GRID DE INFORMAÇÕES
           ============================================ */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-item .label {
            font-size: 11px;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .info-item .value {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-top: 2px;
        }
        
        .info-item .value.status-confirmada { color: #27ae60; }
        .info-item .value.status-pendente { color: #f39c12; }
        .info-item .value.status-cancelada { color: #e74c3c; }
        
        /* ============================================
           SEÇÕES
           ============================================ */
        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        /* ============================================
           TABELA DE INGRESSOS
           ============================================ */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        table thead th {
            background: #f8f9fa;
            padding: 8px 10px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #555555;
            border-bottom: 2px solid #dee2e6;
        }
        
        table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 13px;
        }
        
        table tfoot td {
            border-bottom: none;
            font-weight: bold;
            padding-top: 12px;
            font-size: 15px;
            color: #27ae60;
        }
        
        .total-value {
            font-size: 18px;
            color: #27ae60;
        }
        
        /* ============================================
           QR CODE
           ============================================ */
        .qr-container {
            text-align: center;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .qr-container img {
            max-width: 130px;
            height: auto;
        }
        
        .qr-container .qr-label {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .qr-container .qr-hint {
            font-size: 11px;
            color: #888888;
            margin-top: 8px;
        }
        
        /* ============================================
           BADGES E ETIQUETAS
           ============================================ */
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e9ecef; color: #495057; }
        
        /* ============================================
           CÓDIGOS
           ============================================ */
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            color: #e83e8c;
            font-family: monospace;
        }
        
        /* ============================================
           PAGAMENTO
           ============================================ */
        .payment-box {
            background: #f8f9fa;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 5px 0;
        }
        
        .payment-box .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        
        /* ============================================
           FOOTER
           ============================================ */
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
            text-align: center;
            font-size: 11px;
            color: #999999;
        }
        
        .footer strong {
            color: #e74c3c;
        }
        
        /* ============================================
           UTILITÁRIOS
           ============================================ */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-1 { margin-top: 5px; }
        .mt-2 { margin-top: 10px; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        
        /* ============================================
           QUEBRA DE PÁGINA PARA IMPRESSÃO
           ============================================ */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="container">
        
        {{-- ============================================
        HEADER - LOGO E TÍTULO
        ============================================ --}}
        <div class="header">
            <div class="logo">
                🎫 SHOTGUN MOZ
                <small>Sistema de Gestão de Eventos</small>
            </div>
            <div class="title">COMPROVANTE DE COMPRA</div>
            <div class="subtitle">
                Venda #{{ $venda->id }} | {{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y H:i') }}
            </div>
        </div>
        
        {{-- ============================================
        INFORMAÇÕES DA VENDA
        ============================================ --}}
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Evento</span>
                <span class="value">{{ $venda->evento->nome ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Status</span>
                <span class="value status-{{ $venda->status }}">
                    {{ ucfirst($venda->status) }}
                </span>
            </div>
            <div class="info-item">
                <span class="label">Cliente</span>
                <span class="value">{{ $venda->participante->nome_completo ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Tipo de Venda</span>
                <span class="value">{{ ucfirst($venda->tipo) }}</span>
            </div>
            <div class="info-item">
                <span class="label">Email</span>
                <span class="value">{{ $venda->participante->email ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Telefone</span>
                <span class="value">{{ $venda->participante->telefone ?? 'N/A' }}</span>
            </div>
        </div>
        
        {{-- ============================================
        INGRESSOS
        ============================================ --}}
        <div class="section-title">🎟️ Ingressos</div>
        <table>
            <thead>
                <tr>
                    <th width="30">#</th>
                    <th>Categoria</th>
                    <th>QR Code</th>
                    <th width="80" style="text-align: right;">Preço</th>
                </tr>
            </thead>
            <tbody>
                @forelse($venda->pedido->itens ?? [] as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->categoria->nome ?? 'N/A' }}</td>
                    <td><code>{{ $item->ingresso->qr_code ?? 'N/A' }}</code></td>
                    <td style="text-align: right;">{{ number_format($item->preco_unitario, 2, ',', '.') }} MZN</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999999;">
                        Nenhum ingresso encontrado
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">Total:</td>
                    <td style="text-align: right;" class="total-value">
                        {{ number_format($venda->valor_total, 2, ',', '.') }} MZN
                    </td>
                </tr>
            </tfoot>
        </table>
        
        {{-- ============================================
        PAGAMENTO
        ============================================ --}}
        @if($venda->pedido && $venda->pedido->pagamentos && $venda->pedido->pagamentos->count() > 0)
        <div class="section-title">💳 Pagamento</div>
        @foreach($venda->pedido->pagamentos as $pagamento)
        <div class="payment-box">
            <div class="row">
                <div><strong>Método:</strong> {{ ucfirst($pagamento->metodo) }}</div>
                <div>
                    <strong>Status:</strong>
                    <span class="badge badge-{{ $pagamento->status == 'confirmado' ? 'success' : ($pagamento->status == 'pendente' ? 'warning' : 'danger') }}">
                        {{ ucfirst($pagamento->status) }}
                    </span>
                </div>
            </div>
            @if($pagamento->referencia)
            <div><strong>Referência:</strong> {{ $pagamento->referencia }}</div>
            @endif
            @if($pagamento->codigo_confirmacao)
            <div><strong>Código:</strong> {{ $pagamento->codigo_confirmacao }}</div>
            @endif
            <div><strong>Valor:</strong> {{ number_format($pagamento->valor, 2, ',', '.') }} MZN</div>
            <div><strong>Data:</strong> {{ \Carbon\Carbon::parse($pagamento->created_at)->format('d/m/Y H:i') }}</div>
        </div>
        @endforeach
        @endif
        
        {{-- ============================================
        QR CODE
        ============================================ --}}
        @if($qrCode)
        <div class="qr-container">
            <div class="qr-label">📱 QR Code do Ingresso</div>
            <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">
            <div class="qr-hint">
                Apresente este QR Code na entrada do evento
            </div>
        </div>
        @endif
        
        {{-- ============================================
        FOOTER
        ============================================ --}}
        <div class="footer">
            <p><strong>Shotgun MOZ</strong> - Sistema de Gestão de Eventos</p>
            <p>Este documento é um comprovante oficial de compra.</p>
            <p><small>Documento gerado em {{ now()->format('d/m/Y H:i:s') }}</small></p>
        </div>
        
    </div>
</body>
</html>