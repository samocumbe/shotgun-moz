<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Financeiro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #7f8c8d;
            margin: 5px 0;
        }
        .header .evento {
            font-size: 18px;
            font-weight: bold;
            color: #3498db;
        }

        .card {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .card-title {
            font-weight: bold;
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #27ae60;
        }
        .text-danger {
            color: #e74c3c;
        }
        .text-primary {
            color: #3498db;
        }
        .text-warning {
            color: #f39c12;
        }

        .total {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .total td {
            padding: 12px 8px;
            border-top: 2px solid #2c3e50;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .resumo-item {
            display: inline-block;
            margin-right: 30px;
            text-align: center;
            min-width: 120px;
        }
        .resumo-item .label {
            font-size: 10px;
            color: #7f8c8d;
            display: block;
        }
        .resumo-item .value {
            font-size: 18px;
            font-weight: bold;
            display: block;
        }
        .resumo-container {
            text-align: center;
            padding: 10px 0;
        }

        .row {
            display: flex;
            margin-top: 20px;
        }
        .col-6 {
            width: 48%;
            padding: 0 10px;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>RELATORIO FINANCEIRO</h1>
        <p class="evento">{{ $evento->nome }}</p>
        <p>Gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- RESUMO FINANCEIRO --}}
    <div class="card">
        <div class="card-title">RESUMO FINANCEIRO</div>
        <div class="resumo-container">
            <div class="resumo-item">
                <span class="label">Receita Bruta</span>
                <span class="value text-success">{{ number_format($resumo['receita_bruta'], 2, ',', '.') }} MZN</span>
            </div>
            <div class="resumo-item">
                <span class="label">Total Comissoes</span>
                <span class="value text-danger">{{ number_format($resumo['total_comissoes'], 2, ',', '.') }} MZN</span>
            </div>
            <div class="resumo-item">
                <span class="label">Receita Liquida</span>
                <span class="value text-primary">{{ number_format($resumo['receita_liquida'], 2, ',', '.') }} MZN</span>
            </div>
            <div class="resumo-item">
                <span class="label">Total Vendas</span>
                <span class="value">{{ number_format($resumo['total_vendas'], 0, ',', '.') }}</span>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    {{-- COMISSOES E METODOS DE PAGAMENTO --}}
    <div class="row">
        {{-- Comissoes --}}
        <div class="col-6">
            <div class="card">
                <div class="card-title">COMISSOES</div>
                <table>
                    <tr>
                        <td><strong>Pagas</strong></td>
                        <td class="text-right text-success"><strong>{{ number_format($resumo['comissoes_pagas'], 2, ',', '.') }} MZN</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Pendentes</strong></td>
                        <td class="text-right text-warning"><strong>{{ number_format($resumo['comissoes_pendentes'], 2, ',', '.') }} MZN</strong></td>
                    </tr>
                    <tr class="total">
                        <td><strong>TOTAL</strong></td>
                        <td class="text-right"><strong>{{ number_format($resumo['total_comissoes'], 2, ',', '.') }} MZN</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Metodos de Pagamento --}}
        <div class="col-6">
            <div class="card">
                <div class="card-title">METODOS DE PAGAMENTO</div>
                <table>
                    @foreach($resumo['por_metodo'] as $metodo => $valor)
                    <tr>
                        <td><strong>{{ ucfirst($metodo) }}</strong></td>
                        <td class="text-right">{{ number_format($valor, 2, ',', '.') }} MZN</td>
                    </tr>
                    @endforeach
                    <tr class="total">
                        <td><strong>TOTAL</strong></td>
                        <td class="text-right"><strong>{{ number_format($resumo['receita_bruta'], 2, ',', '.') }} MZN</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- INFORMACOES DO EVENTO --}}
    <div class="card" style="margin-top: 20px;">
        <div class="card-title">INFORMACOES DO EVENTO</div>
        <table>
            <tr>
                <td style="width: 20%;"><strong>Evento</strong></td>
                <td style="width: 30%;">{{ $evento->nome }}</td>
                <td style="width: 20%;"><strong>Local</strong></td>
                <td style="width: 30%;">{{ $evento->local->nome ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Data Inicio</strong></td>
                <td>{{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}</td>
                <td><strong>Data Fim</strong></td>
                <td>{{ \Carbon\Carbon::parse($evento->data_fim)->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Relatorio gerado automaticamente pelo sistema Shotgun MOZ</p>
        <p>{{ \Carbon\Carbon::now()->year }} Shotgun MOZ - Todos os direitos reservados</p>
    </div>

</body>
</html>