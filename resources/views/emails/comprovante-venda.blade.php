{{--
    ============================================================
    VIEW: Email de Comprovante de Venda
    ============================================================
    Esta view será usada para enviar o comprovante por email
    quando a funcionalidade for implementada.
    
    NOTA: Atualmente esta view está pronta mas não está sendo
    utilizada. Será ativada quando o envio de email for
    implementado no VendaController.
    
    Dados disponíveis (futuramente):
    - $cliente_nome: Nome do cliente
    - $evento_nome: Nome do evento
    - $venda_id: ID da venda
    - $valor_total: Valor total formatado
    - $data_compra: Data da compra
    ============================================================
--}}

<!DOCTYPE html>
<html lang="pt-MZ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Compra - Shotgun MOZ</title>
    <style>
        /* ============================================
           RESET E CONFIGURAÇÕES BÁSICAS
           ============================================ */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: #f4f4f4;
            padding: 20px;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }
        
        /* ============================================
           CONTAINER PRINCIPAL
           ============================================ */
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* ============================================
           HEADER
           ============================================ */
        .header {
            text-align: center;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .logo span {
            display: block;
            font-size: 12px;
            color: #888888;
            font-weight: normal;
        }
        
        /* ============================================
           CONTEÚDO
           ============================================ */
        .content {
            padding: 10px 0;
        }
        
        .content p {
            font-size: 14px;
            line-height: 1.6;
            color: #333333;
            margin: 10px 0;
        }
        
        /* ============================================
           CAIXA DE INFORMAÇÕES
           ============================================ */
        .info-box {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #e74c3c;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .info-box .label {
            font-weight: 600;
            color: #555555;
        }
        
        .info-box .highlight {
            color: #e74c3c;
            font-weight: bold;
        }
        
        /* ============================================
           BOTÃO (placeholder)
           ============================================ */
        .btn-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            display: inline-block;
            background: #e74c3c;
            color: #ffffff;
            padding: 10px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #c0392b;
        }
        
        /* ============================================
           ALERTA
           ============================================ */
        .alert-box {
            background: #fff3cd;
            padding: 12px 15px;
            border-radius: 6px;
            border-left: 4px solid #f39c12;
            margin: 15px 0;
        }
        
        .alert-box p {
            font-size: 13px;
            margin: 0;
            color: #856404;
            line-height: 1.5;
        }
        
        /* ============================================
           FOOTER
           ============================================ */
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            color: #888888;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        .footer .footer-brand {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .footer .copyright {
            font-size: 11px;
            color: #aaaaaa;
        }
        
        /* ============================================
           RESPONSIVIDADE
           ============================================ */
        @media only screen and (max-width: 480px) {
            .container {
                padding: 15px;
            }
            .info-box {
                padding: 10px 15px;
            }
            .btn {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    
    <div class="container">
        
        {{-- ============================================
        HEADER
        ============================================ --}}
        <div class="header">
            <div class="logo">
                🎫 SHOTGUN MOZ
                <span>Sistema de Gestão de Eventos</span>
            </div>
        </div>
        
        {{-- ============================================
        CORPO DO EMAIL
        ============================================ --}}
        <div class="content">
            
            {{-- Saudação --}}
            <p>Olá <strong>{{ $cliente_nome ?? 'Cliente' }}</strong>,</p>
            
            <p>
                Obrigado por confiar na <strong>Shotgun MOZ</strong>! 
                Sua compra foi realizada com sucesso.
            </p>
            
            {{-- Resumo da Compra --}}
            <div class="info-box">
                <p><span class="label">📌 Venda #{{ $venda_id ?? 'N/A' }}</span></p>
                <p><span class="label">🎪 Evento:</span> {{ $evento_nome ?? 'Evento' }}</p>
                <p><span class="label">💰 Valor Total:</span> <strong class="highlight">{{ $valor_total ?? 'N/A' }}</strong></p>
                <p><span class="label">📅 Data:</span> {{ $data_compra ?? 'N/A' }}</p>
            </div>
            
            {{-- Informação do Anexo --}}
            <p>
                Em anexo você encontrará o PDF com o comprovante completo da sua compra.
                Este documento contém todos os detalhes do seu pedido.
            </p>
            
            {{-- Botão (placeholder - futuramente será um link real) --}}
            <div class="btn-container">
                <a href="#" class="btn">📱 Ver Meus Ingressos</a>
            </div>
            
            {{-- Alerta Importante --}}
            <div class="alert-box">
                <p>
                    <strong>⚠️ Importante:</strong> Apresente o QR Code no dia do evento para validar sua entrada. 
                    O QR Code está disponível no PDF em anexo.
                </p>
            </div>
            
            {{-- Agradecimento --}}
            <p style="margin-top: 15px;">
                <strong>Equipe Shotgun MOZ</strong>
                <br>
                <span style="font-size: 13px; color: #666666;">
                    Estamos à disposição para qualquer dúvida.
                </span>
            </p>
            
        </div>
        
        {{-- ============================================
        FOOTER
        ============================================ --}}
        <div class="footer">
            <p>
                © {{ date('Y') }} <span class="footer-brand">Shotgun MOZ</span> - Todos os direitos reservados
            </p>
            <p>Este email foi enviado automaticamente. Por favor, não responda.</p>
            <p class="copyright">
                Shotgun MOZ - Sistema de Gestão de Eventos para Moçambique
            </p>
        </div>
        
    </div>
    
</body>
</html>