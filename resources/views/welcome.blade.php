{{--
    ============================================================
    VIEW: Página Inicial (Welcome) - SHOTGUN-MOZ
    ============================================================
    Landing page pública com foco em conversão.

    Secções:
    1. Navbar (fixa no topo)
    2. Hero (chamada principal + CTAs)
    3. Estatísticas (eventos, ingressos, utilizadores)
    4. Próximos eventos (dinâmico, se existirem)
    5. Como funciona (3 passos)
    6. CTA para organizadores
    7. Selo de pagamentos (M-Pesa, e-Mola)
    8. Footer

    Se não houver eventos na BD, a secção "Próximos eventos"
    mostra um estado vazio apelativo.
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SHOTGUN-MOZ — Plataforma de venda de ingressos para eventos em Moçambique. Compra, organiza e participa.">

    <title>SHOTGUN-MOZ — Ingressos para os melhores eventos</title>

    {{-- Fontes --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    {{-- Ícones --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Tailwind (já vem com o Breeze) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand: #1a73e8;
            --brand-dark: #0d47a1;
            --brand-light: #e3f2fd;
            --accent: #f39c12;
            --dark: #0f172a;
            --gray: #64748b;
        }

        body {
            font-family: 'Figtree', system-ui, sans-serif;
            color: var(--dark);
            -webkit-font-smoothing: antialiased;
        }

        /* NAVBAR */
        .navbar-landing {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
        }
        .logo-landing {
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--dark);
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .logo-landing span { color: var(--brand); }

        /* BOTÕES */
        .btn-brand {
            background: var(--brand);
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        .btn-brand:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(26,115,232,0.3);
            color: #fff;
        }
        .btn-outline-brand {
            background: transparent;
            color: var(--brand);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 2px solid var(--brand);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-outline-brand:hover {
            background: var(--brand);
            color: #fff;
        }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%);
            padding: 5rem 0 4rem;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(26,115,232,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 1.5rem;
        }
        .hero-title span {
            background: linear-gradient(135deg, var(--brand), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-subtitle {
            font-size: 1.125rem;
            color: var(--gray);
            margin-bottom: 2rem;
            max-width: 32rem;
        }

        /* STATS */
        .stat-item {
            text-align: center;
            padding: 1.5rem 1rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--brand);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* CARDS DE EVENTO */
        .event-card {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }
        .event-image {
            height: 180px;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 3rem;
        }
        .event-body {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .event-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        .event-meta {
            font-size: 0.875rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.375rem;
        }
        .event-price {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--brand);
            margin-top: auto;
        }
        .event-date-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: #fff;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .event-date-day {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--brand);
            line-height: 1;
        }
        .event-date-month {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* COMO FUNCIONA */
        .step-card {
            text-align: center;
            padding: 2rem 1.5rem;
        }
        .step-number {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            background: var(--brand-light);
            color: var(--brand);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        /* CTA ORGANIZADOR */
        .cta-organizador {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            color: #fff;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }
        .cta-organizador::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        /* FOOTER */
        .footer-landing {
            background: var(--dark);
            color: rgba(255,255,255,0.7);
            padding: 3rem 0 1.5rem;
        }
        .footer-landing a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-landing a:hover {
            color: #fff;
        }
        .payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.05);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* RESPONSIVIDADE */
        @media (max-width: 640px) {
            .hero { padding: 3rem 0 2rem; }
            .stat-number { font-size: 2rem; }
        }
    </style>
</head>
<body class="antialiased bg-white">

{{-- ============================================================
     NAVBAR
     ============================================================ --}}
<nav class="navbar-landing">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="logo-landing">
                🎯 Shotgun <span>MOZ</span>
            </a>

            {{-- Links desktop --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#eventos" class="text-sm font-medium text-gray-600 hover:text-gray-900">Eventos</a>
                <a href="#como-funciona" class="text-sm font-medium text-gray-600 hover:text-gray-900">Como Funciona</a>
                <a href="#organizadores" class="text-sm font-medium text-gray-600 hover:text-gray-900">Organiza</a>
            </div>

            {{-- Ações (login/registo) --}}
            <div class="flex items-center gap-3">
                @auth
                    {{-- Já autenticado --}}
                    <a href="{{ route('admin.dashboard') }}" class="btn-brand text-sm">
                        <i class="bi bi-speedometer2"></i> Painel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 hidden sm:inline">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn-brand text-sm">
                        <i class="bi bi-person-plus"></i> Criar Conta
                    </a>
                @endauth
            </div>

        </div>
    </div>
</nav>

{{-- ============================================================
     HERO
     ============================================================ --}}
<section class="hero">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid md:grid-cols-2 gap-12 items-center">

            {{-- Texto --}}
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white rounded-full text-sm font-semibold text-gray-700 mb-6 shadow-sm">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    Plataforma #1 de eventos em Moçambique
                </div>

                <h1 class="hero-title">
                    Os melhores eventos de <span>Moçambique</span>, a um clique de distância.
                </h1>

                <p class="hero-subtitle">
                    Compra ingressos com segurança, recebe o QR code no telemóvel
                    e entra sem filas. Simples, rápido e 100% digital.
                </p>

                <div class="flex flex-wrap gap-3">
                    <a href="#eventos" class="btn-brand">
                        <i class="bi bi-ticket-perforated"></i> Descobrir Eventos
                    </a>
                    <a href="#organizadores" class="btn-outline-brand">
                        <i class="bi bi-calendar-plus"></i> Criar o meu evento
                    </a>
                </div>

                <div class="flex items-center gap-6 mt-8 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-shield-check text-green-600"></i>
                        <span>Pagamento seguro</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="bi bi-lightning-charge text-yellow-500"></i>
                        <span>QR imediato</span>
                    </div>
                </div>
            </div>

            {{-- Ilustração --}}
            <div class="hidden md:block relative">
                <div class="relative w-full max-w-md mx-auto">
                    {{-- Mockup de ingresso --}}
                    <div style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); border-radius: 1.5rem; padding: 2rem; color: #fff; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); transform: rotate(-3deg);">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div style="font-size: 0.75rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px;">Ingresso Digital</div>
                                <div style="font-size: 1.5rem; font-weight: 800; margin-top: 0.25rem;">Festival MOZ 2026</div>
                            </div>
                            <i class="bi bi-ticket-perforated" style="font-size: 2rem; opacity: 0.9;"></i>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div>
                                <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Data</div>
                                <div style="font-weight: 700; margin-top: 0.25rem;">20 Dez 2026</div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Local</div>
                                <div style="font-weight: 700; margin-top: 0.25rem;">Maputo</div>
                            </div>
                        </div>

                        {{-- QR simulado --}}
                        <div style="background: #fff; border-radius: 0.75rem; padding: 1rem; display: flex; justify-content: center;">
                            <div style="width: 120px; height: 120px; background: repeating-linear-gradient(0deg, #000, #000 4px, #fff 4px, #fff 8px), repeating-linear-gradient(90deg, #000, #000 4px, #fff 4px, #fff 8px); background-blend-mode: multiply;"></div>
                        </div>
                    </div>

                    {{-- Badge flutuante --}}
                    <div style="position: absolute; bottom: -20px; left: -20px; background: #fff; border-radius: 1rem; padding: 1rem 1.25rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-check-lg" style="color: #16a34a; font-size: 1.25rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.875rem;">Compra confirmada</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Recebido em 2s</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     ESTATÍSTICAS
     ============================================================ --}}
<section class="py-12 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                // Estatísticas dinâmicas (com fallback para valores "de apresentação")
                $totalEventos    = \App\Models\Evento::where('status', 'publicado')->count();
                $totalIngressos  = \App\Models\Ingresso::where('status', 'pago')->count();
                $totalUsers      = \App\Models\User::count();
                $totalOrganizacoes = \App\Models\Organizacao::where('status', 'ativo')->count();
            @endphp

            <div class="stat-item">
                <div class="stat-number">{{ $totalEventos > 0 ? $totalEventos : '50' }}+</div>
                <div class="stat-label">Eventos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $totalIngressos > 0 ? number_format($totalIngressos) : '10k' }}+</div>
                <div class="stat-label">Ingressos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $totalUsers > 0 ? $totalUsers : '5k' }}+</div>
                <div class="stat-label">Utilizadores</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $totalOrganizacoes > 0 ? $totalOrganizacoes : '20' }}+</div>
                <div class="stat-label">Organizações</div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     PRÓXIMOS EVENTOS
     ============================================================ --}}
<section id="eventos" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Próximos Eventos</h2>
                <p class="text-gray-600">Não percas os eventos mais esperados do país</p>
            </div>
            <a href="{{ route('login') }}" class="hidden md:inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800">
                Ver todos <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @php
            $eventosDestaque = \App\Models\Evento::with('local')
                ->where('status', 'publicado')
                ->where('data_fim', '>=', now())
                ->orderBy('data_inicio', 'asc')
                ->take(3)
                ->get();
        @endphp

        @if($eventosDestaque->count() > 0)
            <div class="grid md:grid-cols-3 gap-6">
                @foreach($eventosDestaque as $evento)
                    <div class="event-card">
                        <div class="event-image">
                            <i class="bi bi-calendar-event"></i>
                            {{-- Data --}}
                            <div class="event-date-badge">
                                <div class="event-date-day">{{ $evento->data_inicio->format('d') }}</div>
                                <div class="event-date-month">{{ $evento->data_inicio->format('M') }}</div>
                            </div>
                        </div>
                        <div class="event-body">
                            <h3 class="event-title">{{ Str::limit($evento->nome, 50) }}</h3>
                            <div class="event-meta">
                                <i class="bi bi-geo-alt"></i>
                                {{ $evento->local->nome ?? 'Local a confirmar' }}
                            </div>
                            <div class="event-meta">
                                <i class="bi bi-clock"></i>
                                {{ $evento->hora_inicio ? $evento->hora_inicio->format('H:i') : 'A confirmar' }}
                            </div>

                            <div class="mt-4 flex justify-between items-center">
                                <div class="event-price">
                                    @if($evento->categoriasIngressos->count() > 0)
                                        desde {{ number_format($evento->categoriasIngressos->min('preco'), 0, ',', '.') }} MT
                                    @else
                                        Ver ingressos
                                    @endif
                                </div>
                                <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                                    Comprar <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Estado vazio --}}
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-4">
                    <i class="bi bi-calendar-x text-3xl text-blue-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Em breve teremos novos eventos</h3>
                <p class="text-gray-600 mb-6">Enquanto isso, explora a plataforma ou organiza o teu evento.</p>
                <a href="{{ route('register') }}" class="btn-brand">
                    <i class="bi bi-person-plus"></i> Criar Conta Grátis
                </a>
            </div>
        @endif

    </div>
</section>

{{-- ============================================================
     COMO FUNCIONA
     ============================================================ --}}
<section id="como-funciona" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Como Funciona</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Em três passos simples, estás dentro do evento.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">

            <div class="step-card">
                <div class="step-number">1</div>
                <h3 class="text-lg font-bold mb-2">Escolhe o Evento</h3>
                <p class="text-gray-600 text-sm">
                    Navega pelos eventos disponíveis, vê os detalhes e escolhe
                    o que mais te agrada.
                </p>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <h3 class="text-lg font-bold mb-2">Paga com Segurança</h3>
                <p class="text-gray-600 text-sm">
                    Paga via M-Pesa, e-Mola ou transferência bancária.
                    Tudo encriptado e seguro.
                </p>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <h3 class="text-lg font-bold mb-2">Recebe o QR Code</h3>
                <p class="text-gray-600 text-sm">
                    O QR chega ao teu telemóvel. Apresenta-o à entrada
                    e entra sem filas.
                </p>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     CTA PARA ORGANIZADORES
     ============================================================ --}}
<section id="organizadores" class="cta-organizador">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-2xl">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4">
                Organizas eventos?
            </h2>
            <p class="text-lg mb-8" style="opacity: 0.9;">
                Cria a tua conta em menos de 2 minutos, publica eventos, controla
                vendas em tempo real e recebe os pagamentos diretamente.
                Tudo numa só plataforma.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('register') }}" style="background: #fff; color: #0d47a1; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-rocket-takeoff"></i> Começar Grátis
                </a>
                <a href="{{ route('login') }}" style="background: transparent; color: #fff; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; border: 2px solid #fff;">
                    <i class="bi bi-box-arrow-in-right"></i> Já tenho conta
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     SELO DE PAGAMENTOS
     ============================================================ --}}
<section class="py-12 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
            Pagamentos aceites
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <div class="payment-badge" style="background: #fef3c7; color: #92400e;">
                <i class="bi bi-phone"></i> M-Pesa
            </div>
            <div class="payment-badge" style="background: #dbeafe; color: #1e40af;">
                <i class="bi bi-phone"></i> e-Mola
            </div>
            <div class="payment-badge" style="background: #f3e8ff; color: #6b21a8;">
                <i class="bi bi-bank"></i> Transferência
            </div>
            <div class="payment-badge" style="background: #dcfce7; color: #14532d;">
                <i class="bi bi-credit-card"></i> Cartão
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     FOOTER
     ============================================================ --}}
<footer class="footer-landing">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 mb-8">

            {{-- Logo + descrição --}}
            <div class="md:col-span-2">
                <div style="font-size: 1.25rem; font-weight: 800; color: #fff; margin-bottom: 0.75rem;">
                    🎯 Shotgun <span style="color: #4fc3f7;">MOZ</span>
                </div>
                <p class="text-sm max-w-md">
                    A plataforma que liga organizadores e público em Moçambique.
                    Compra, organiza e vive os melhores eventos do país.
                </p>
            </div>

            {{-- Links --}}
            <div>
                <h4 class="text-white font-bold text-sm mb-3 uppercase tracking-wider">Plataforma</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#eventos">Eventos</a></li>
                    <li><a href="#como-funciona">Como funciona</a></li>
                    <li><a href="#organizadores">Para organizadores</a></li>
                </ul>
            </div>

            {{-- Contacto --}}
            <div>
                <h4 class="text-white font-bold text-sm mb-3 uppercase tracking-wider">Contacto</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="mailto:info@shotgun-moz.co.mz"><i class="bi bi-envelope"></i> info@shotgun-moz.co.mz</a></li>
                    <li><a href="tel:+258840000000"><i class="bi bi-phone"></i> +258 84 000 0000</a></li>
                    <li><i class="bi bi-geo-alt"></i> Maputo, Moçambique</li>
                </ul>
            </div>

        </div>

        <div class="pt-6 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
            <div>© {{ date('Y') }} SHOTGUN-MOZ. Todos os direitos reservados.</div>
            <div class="flex gap-4">
                <a href="#">Termos</a>
                <a href="#">Privacidade</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>