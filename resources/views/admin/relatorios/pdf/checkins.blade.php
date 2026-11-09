<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Check-ins</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            color: white;
        }
        .badge-success { background-color: #27ae60; }
        .badge-warning { background-color: #f39c12; }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .resumo {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .resumo-item {
            display: inline-block;
            margin-right: 30px;
        }
        .resumo-item .label {
            font-size: 10px;
            color: #7f8c8d;
        }
        .resumo-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATORIO DE CHECK-INS</h1>
        <p class="evento">{{ $evento->nome }}</p>
        <p>Gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="resumo">
        <div class="resumo-item">
            <div class="label">Total de Check-ins</div>
            <div class="value">{{ number_format($totais['total_checkins'], 0, ',', '.') }}</div>
        </div>
        <div class="resumo-item">
            <div class="label">Evento</div>
            <div class="value" style="font-size: 14px;">{{ $evento->nome }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">QR Code</th>
                <th style="width: 25%;">Participante</th>
                <th style="width: 15%;">Porta</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 15%;">Data/Hora</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checkins as $checkin)
            <tr>
                <td>{{ $checkin->id }}</td>
                <td><code>{{ $checkin->qr_code }}</code></td>
                <td>{{ $checkin->ingresso->participante->nome_completo ?? 'N/A' }}</td>
                <td>{{ $checkin->porta_acesso ?? 'N/A' }}</td>
                <td>
                    @if($checkin->status == 'sincronizado')
                        <span class="badge badge-success">Sincronizado</span>
                    @else
                        <span class="badge badge-warning">Pendente</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($checkin->data_checkin)->format('d/m/Y H:i') }}</td>
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