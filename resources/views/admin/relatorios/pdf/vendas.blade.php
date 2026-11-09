<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Vendas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #2c3e50; }
        .header h1 { color: #2c3e50; margin: 0; font-size: 24px; }
        .header p { color: #7f8c8d; margin: 5px 0; }
        .header .evento { font-size: 18px; font-weight: bold; color: #3498db; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th { background-color: #2c3e50; color: white; padding: 10px 8px; text-align: left; font-size: 11px; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
        table tr:nth-child(even) { background-color: #f9f9f9; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #27ae60; }

        .badge { padding: 2px 8px; border-radius: 12px; font-size: 10px; color: white; }
        .badge-primary { background-color: #3498db; }
        .badge-success { background-color: #27ae60; }
        .badge-warning { background-color: #f39c12; }
        .badge-danger { background-color: #e74c3c; }

        .footer { margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }

        .resumo { margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; }
        .resumo-item { display: inline-block; margin-right: 30px; }
        .resumo-item .label { font-size: 10px; color: #7f8c8d; }
        .resumo-item .value { font-size: 16px; font-weight: bold; color: #2c3e50; }

        .total-row { font-weight: bold; background-color: #f8f9fa; }
        .total-row td { padding: 12px 8px; border-top: 2px solid #2c3e50; }
        .total-label { font-size: 14px; color: #2c3e50; }
        .total-value { font-size: 20px; color: #27ae60; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATORIO DE VENDAS</h1>
        <p class="evento">{{ $evento->nome }}</p>
        <p>Gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="resumo">
        <div class="resumo-item">
            <div class="label">Total de Vendas</div>
            <div class="value">{{ number_format($totais['total_vendas'], 0, ',', '.') }}</div>
        </div>
        <div class="resumo-item">
            <div class="label">Valor Medio</div>
            <div class="value">{{ number_format($totais['valor_medio'], 2, ',', '.') }} MZN</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Cliente</th>
                <th style="width: 20%;">Valor (MZN)</th>
                <th style="width: 15%;">Tipo</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 15%;">Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $venda)
            <tr>
                <td>{{ $venda->id }}</td>
                <td>{{ $venda->participante->nome_completo ?? 'N/A' }}</td>
                <td class="text-right"><strong>{{ number_format($venda->valor_total, 2, ',', '.') }}</strong></td>
                <td>
                    <span class="badge badge-primary">{{ ucfirst($venda->tipo) }}</span>
                </td>
                <td>
                    @if($venda->status == 'confirmada')
                        <span class="badge badge-success">Confirmada</span>
                    @elseif($venda->status == 'pendente')
                        <span class="badge badge-warning">Pendente</span>
                    @elseif($venda->status == 'cancelada')
                        <span class="badge badge-danger">Cancelada</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($venda->status) }}</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">
                    <span class="total-label">TOTAL GERAL</span>
                </td>
                <td class="text-right">
                    <span class="total-value">{{ number_format($totais['valor_total'], 2, ',', '.') }} MZN</span>
                </td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Relatorio gerado automaticamente pelo sistema Shotgun MOZ</p>
        <p>{{ \Carbon\Carbon::now()->year }} Shotgun MOZ - Todos os direitos reservados</p>
    </div>
</body>
</html>