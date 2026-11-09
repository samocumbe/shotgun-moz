<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório - {{ $promoter->nome }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #2c3e50; }
        .header h1 { color: #2c3e50; margin: 0; font-size: 24px; }
        .header p { color: #7f8c8d; margin: 5px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background-color: #2c3e50; color: white; padding: 8px; text-align: left; font-size: 11px; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
        table tr:nth-child(even) { background-color: #f9f9f9; }
        
        .text-right { text-align: right; }
        .text-success { color: #27ae60; }
        .badge { padding: 2px 8px; border-radius: 12px; font-size: 10px; color: white; }
        .badge-success { background-color: #27ae60; }
        .badge-warning { background-color: #f39c12; }
        .badge-danger { background-color: #e74c3c; }
        
        .footer { margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        .resumo-item { display: inline-block; margin-right: 30px; }
        .resumo-item .label { font-size: 10px; color: #7f8c8d; }
        .resumo-item .value { font-size: 16px; font-weight: bold; color: #2c3e50; }
        .total { font-weight: bold; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATORIO DO PROMOTER</h1>
        <p><strong>{{ $promoter->nome }}</strong></p>
        <p>Gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- Dados do Promoter --}}
    <table style="margin-top: 10px;">
        <tr>
            <td style="width: 15%;"><strong>Nome:</strong></td>
            <td style="width: 35%;">{{ $promoter->nome }}</td>
            <td style="width: 15%;"><strong>Email:</strong></td>
            <td style="width: 35%;">{{ $promoter->email }}</td>
        </tr>
        <tr>
            <td><strong>Código:</strong></td>
            <td>{{ $promoter->codigo_promocional }}</td>
            <td><strong>Status:</strong></td>
            <td>{{ ucfirst($promoter->status) }}</td>
        </tr>
        <tr>
            <td><strong>Comissão:</strong></td>
            <td>{{ number_format($promoter->comissao_padrao, 0) }}%</td>
            <td><strong>Total Vendas:</strong></td>
            <td>{{ number_format($resumo['total_vendas'], 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- Resumo Financeiro --}}
    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
        <div class="resumo-item">
            <div class="label">Valor Total</div>
            <div class="value">{{ number_format($resumo['valor_total'], 2, ',', '.') }} MZN</div>
        </div>
        <div class="resumo-item">
            <div class="label">Comissão Total</div>
            <div class="value text-success">{{ number_format($resumo['comissao_total'], 2, ',', '.') }} MZN</div>
        </div>
        <div class="resumo-item">
            <div class="label">Comissões Pagas</div>
            <div class="value" style="color: #27ae60;">{{ number_format($resumo['comissoes_pagas'], 2, ',', '.') }} MZN</div>
        </div>
        <div class="resumo-item">
            <div class="label">Comissões Pendentes</div>
            <div class="value" style="color: #f39c12;">{{ number_format($resumo['comissoes_pendentes'], 2, ',', '.') }} MZN</div>
        </div>
    </div>

    {{-- Vendas por Evento --}}
    <h3 style="margin-top: 20px;">Vendas por Evento</h3>
    <table>
        <thead>
            <tr>
                <th>Evento</th>
                <th class="text-right">Quantidade</th>
                <th class="text-right">Valor (MZN)</th>
                <th class="text-right">Comissão (MZN)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas_por_evento as $venda)
            <tr>
                <td>{{ $venda['evento'] }}</td>
                <td class="text-right">{{ $venda['quantidade'] }}</td>
                <td class="text-right">{{ number_format($venda['valor'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($venda['comissao'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Histórico de Comissões --}}
    <h3 style="margin-top: 20px;">Histórico de Comissões</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pedido</th>
                <th class="text-right">Valor Venda</th>
                <th class="text-right">%</th>
                <th class="text-right">Comissão</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comissoes as $comissao)
            <tr>
                <td>{{ $comissao->id }}</td>
                <td>{{ $comissao->pedido->numero_pedido ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($comissao->valor_venda, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($comissao->percentual_comissao, 0) }}</td>
                <td class="text-right"><strong>{{ number_format($comissao->valor_comissao, 2, ',', '.') }}</strong></td>
                <td>
                    @if($comissao->status == 'pago')
                        <span class="badge badge-success">Pago</span>
                    @elseif($comissao->status == 'pendente')
                        <span class="badge badge-warning">Pendente</span>
                    @elseif($comissao->status == 'cancelado')
                        <span class="badge badge-danger">Cancelado</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($comissao->created_at)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Relatorio gerado automaticamente pelo sistema Shotgun MOZ</p>
        <p>{{ \Carbon\Carbon::now()->year }} Shotgun MOZ - Todos os direitos reservados</p>
    </div>
</body>
</html>