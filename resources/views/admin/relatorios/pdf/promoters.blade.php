<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Promoters</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #2c3e50; }
        .header h1 { color: #2c3e50; margin: 0; font-size: 24px; }
        .header p { color: #7f8c8d; margin: 5px 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th { background-color: #2c3e50; color: white; padding: 10px 8px; text-align: left; font-size: 11px; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
        table tr:nth-child(even) { background-color: #f9f9f9; }

        .text-right { text-align: right; }
        .text-success { color: #27ae60; }
        .text-warning { color: #f39c12; }

        .badge { padding: 2px 8px; border-radius: 12px; font-size: 10px; color: white; }
        .badge-success { background-color: #27ae60; }
        .badge-danger { background-color: #e74c3c; }
        .badge-warning { background-color: #f39c12; }

        .footer { margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        .total { font-weight: bold; background-color: #f8f9fa; }
        .total td { padding: 12px 8px; border-top: 2px solid #2c3e50; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATORIO DE PROMOTERS</h1>
        <p>Gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Codigo</th>
                <th>Comissao</th>
                <th>Vendas</th>
                <th>Comissao Recebida</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estatisticas as $promoter)
            <tr>
                <td><strong>{{ $promoter['nome'] }}</strong></td>
                <td>{{ $promoter['email'] }}</td>
                <td><code>{{ $promoter['codigo'] }}</code></td>
                <td>{{ number_format($promoter['comissao_padrao'], 0) }}%</td>
                <td>{{ number_format($promoter['total_vendas'], 2, ',', '.') }} MZN</td>
                <td>
                    <span class="text-success">{{ number_format($promoter['comissoes_pagas'], 2, ',', '.') }} MZN</span>
                    <br>
                    <small class="text-warning">Pendente: {{ number_format($promoter['comissoes_pendentes'], 2, ',', '.') }} MZN</small>
                </td>
                <td>
                    @if($promoter['status'] == 'ativo')
                        <span class="badge badge-success">Ativo</span>
                    @elseif($promoter['status'] == 'inativo')
                        <span class="badge badge-danger">Inativo</span>
                    @elseif($promoter['status'] == 'pendente')
                        <span class="badge badge-warning">Pendente</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="4" class="text-right"><strong>TOTAIS</strong></td>
                <td><strong>{{ number_format($totais['total_vendas'], 2, ',', '.') }} MZN</strong></td>
                <td><strong>{{ number_format($totais['total_comissoes'], 2, ',', '.') }} MZN</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Relatorio gerado automaticamente pelo sistema Shotgun MOZ</p>
        <p>{{ \Carbon\Carbon::now()->year }} Shotgun MOZ - Todos os direitos reservados</p>
    </div>
</body>
</html>