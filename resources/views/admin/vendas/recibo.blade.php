<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo - Venda #{{ $venda->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #e74c3c; }
        .title { font-size: 20px; margin: 20px 0; }
        .info { margin: 20px 0; }
        .info table { width: 100%; }
        .info td { padding: 5px 0; }
        .items { margin: 30px 0; }
        .items table { width: 100%; border-collapse: collapse; }
        .items th, .items td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .items th { background: #f5f5f5; }
        .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 20px; }
        .footer { text-align: center; margin-top: 40px; border-top: 1px solid #ddd; padding-top: 20px; color: #666; font-size: 12px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 3px; color: white; }
        .status-confirmada { background: #28a745; }
        .status-pendente { background: #ffc107; color: #333; }
        .status-cancelada { background: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🎫 SHOTGUN MOZ</div>
        <h2>COMPROVANTE DE COMPRA</h2>
        <p><strong>Venda #{{ $venda->id }}</strong></p>
        <p>Data: {{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>Evento:</strong></td>
                <td>{{ $venda->evento->nome ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Cliente:</strong></td>
                <td>{{ $venda->participante->nome_completo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td>{{ $venda->participante->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Telefone:</strong></td>
                <td>{{ $venda->participante->telefone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Tipo de Venda:</strong></td>
                <td>{{ ucfirst($venda->tipo) }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>
                    <span class="status-badge status-{{ $venda->status }}">
                        {{ ucfirst($venda->status) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="items">
        <h3>Ingressos</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Categoria</th>
                    <th>QR Code</th>
                    <th>Preço</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venda->pedido->itens ?? [] as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->categoria->nome ?? 'N/A' }}</td>
                    <td><code>{{ $item->ingresso->qr_code ?? 'N/A' }}</code></td>
                    <td>{{ number_format($item->preco_unitario, 2, ',', '.') }} MZN</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>{{ number_format($venda->valor_total, 2, ',', '.') }} MZN</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($venda->pedido && $venda->pedido->pagamentos)
    <div class="info">
        <h3>Pagamento</h3>
        @foreach($venda->pedido->pagamentos as $pagamento)
        <table>
            <tr>
                <td><strong>Método:</strong></td>
                <td>{{ ucfirst($pagamento->metodo) }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>{{ ucfirst($pagamento->status) }}</td>
            </tr>
            @if($pagamento->referencia)
            <tr>
                <td><strong>Referência:</strong></td>
                <td>{{ $pagamento->referencia }}</td>
            </tr>
            @endif
        </table>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>Este documento é um comprovante oficial de compra.</p>
        <p>Shotgun MOZ - Sistema de Gestão de Eventos</p>
        <p><small>Documento gerado em {{ now()->format('d/m/Y H:i:s') }}</small></p>
    </div>

    <script>
        // Auto print quando a página carregar
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>