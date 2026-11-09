{{--
    ============================================================
    LAYOUT: Painel Administrativo - SHOTGUN-MOZ
    ============================================================
    Layout moderno com sidebar colapsável e menu inteligente (RBAC).
    Itens do menu desaparecem consoante o papel do utilizador.
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Shotgun MOZ - Painel Admin')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed: 70px;
            --primary-color: #1a73e8;
            --secondary-color: #2c3e50;
            --bg-color: #f0f2f5;
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            --transition-speed: 0.3s;
        }

        body {
            font-size: .875rem;
            background-color: var(--bg-color);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }

        .sidebar-wrapper {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            z-index: 1050;
            background: linear-gradient(180deg, #1a2332 0%, #2c3e50 100%);
            transition: width var(--transition-speed) ease;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.15);
        }

        .sidebar-wrapper.collapsed { width: var(--sidebar-collapsed); }
        .sidebar-wrapper.collapsed .sidebar-text { display: none; }
        .sidebar-wrapper.collapsed .sidebar-logo h5,
        .sidebar-wrapper.collapsed .sidebar-logo small { display: none; }
        .sidebar-wrapper.collapsed .nav-link { justify-content: center; padding: 0.75rem 0; }
        .sidebar-wrapper.collapsed .nav-link i { margin-right: 0; font-size: 1.3rem; }
        .sidebar-wrapper.collapsed .nav-link .badge { display: none; }
        .sidebar-wrapper.collapsed .menu-divider span { display: none; }
        .sidebar-wrapper.collapsed .menu-divider { text-align: center; padding: 0.5rem 0; }
        .sidebar-wrapper.collapsed .menu-divider::after { content: "···"; color: rgba(255,255,255,0.3); }
        .sidebar-wrapper.collapsed .collapse { display: none !important; }

        .sidebar { padding: 0 0 1rem 0; height: 100%; }

        .sidebar-logo {
            padding: 1.2rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-logo h5 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .sidebar-logo h5 span { color: #4fc3f7; }
        .sidebar-logo small {
            color: rgba(255,255,255,0.5);
            font-size: 0.7rem;
            display: block;
            margin-top: 2px;
        }

        .sidebar-toggle {
            position: absolute;
            top: 50%; right: -14px;
            transform: translateY(-50%);
            width: 28px; height: 28px;
            border-radius: 50%;
            background: var(--primary-color);
            border: none; color: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 10;
            box-shadow: 0 2px 8px rgba(26,115,232,0.4);
            transition: transform var(--transition-speed) ease;
        }
        .sidebar-toggle:hover { transform: translateY(-50%) scale(1.1); }
        .sidebar-wrapper.collapsed .sidebar-toggle { transform: translateY(-50%) rotate(180deg); }

        .menu-divider {
            padding: 0.75rem 1.5rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            letter-spacing: 1px;
            font-weight: 600;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.65rem 1.2rem;
            border-left: 3px solid transparent;
            transition: all var(--transition-speed) ease;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            white-space: nowrap;
            gap: 12px;
        }
        .sidebar .nav-link i { font-size: 1.15rem; width: 24px; text-align: center; flex-shrink: 0; color: rgba(255,255,255,0.5); }
        .sidebar .nav-link .sidebar-text { flex: 1; }
        .sidebar .nav-link .badge { font-size: 0.6rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
        .sidebar .nav-link:hover { color: #fff; background-color: rgba(255,255,255,0.08); border-left-color: var(--primary-color); }
        .sidebar .nav-link.active { color: #fff; background-color: rgba(255,255,255,0.1); border-left-color: var(--primary-color); }
        .sidebar .nav-link.active i { color: #4fc3f7; }

        .menu-separator { margin: 0.5rem 1rem; border: 0; border-top: 1px solid rgba(255,255,255,0.06); }

        .sidebar .nav-link[data-bs-toggle="collapse"] { cursor: pointer; }
        .sidebar .nav-link[data-bs-toggle="collapse"] .bi-chevron-down { transition: transform 0.3s ease; }
        .sidebar .nav-link[data-bs-toggle="collapse"][aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
        .sidebar .collapse .nav-link { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .sidebar .collapse .nav-link:hover { background-color: rgba(255,255,255,0.05); }
        .sidebar .collapse .nav-link.active { background-color: rgba(26,115,232,0.15); border-left-color: #4fc3f7; }

        .main-content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding-top: 60px;
            padding-bottom: 30px;
            background-color: var(--bg-color);
            transition: margin-left var(--transition-speed) ease;
        }
        .main-content-wrapper.expanded { margin-left: var(--sidebar-collapsed); }
        .main-content { padding: 20px 30px; overflow-x: auto; min-height: calc(100vh - 90px); }

        .top-navbar {
            position: fixed;
            top: 0; left: var(--sidebar-width); right: 0;
            z-index: 1040;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: left var(--transition-speed) ease;
        }
        .top-navbar.expanded { left: var(--sidebar-collapsed); }
        .top-navbar .navbar-left { display: flex; align-items: center; gap: 16px; }
        .top-navbar .navbar-left .page-title { font-size: 1.1rem; font-weight: 600; color: #1a2332; margin: 0; }
        .top-navbar .navbar-right { display: flex; align-items: center; gap: 16px; }
        .top-navbar .navbar-right .nav-icon { position: relative; color: #5f6b7a; font-size: 1.2rem; text-decoration: none; padding: 4px; }
        .top-navbar .navbar-right .nav-icon:hover { color: var(--primary-color); }
        .top-navbar .navbar-right .nav-icon .badge { position: absolute; top: -4px; right: -4px; font-size: 0.55rem; padding: 2px 6px; border-radius: 10px; }

        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .user-dropdown .dropdown-toggle::after { display: none; }
        .user-dropdown .dropdown-menu { border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border: none; padding: 8px 0; min-width: 200px; }
        .user-dropdown .dropdown-item { padding: 8px 20px; font-size: 0.85rem; color: #333; display: flex; align-items: center; gap: 10px; }
        .user-dropdown .dropdown-item:hover { background-color: #f0f2f5; }

        .card { border-radius: 12px; box-shadow: var(--card-shadow); border: none; margin-bottom: 20px; background: #fff; }
        .card-header { background-color: transparent; border-bottom: 1px solid rgba(0,0,0,0.05); font-weight: 600; padding: 16px 20px; }
        .card-body { padding: 20px; }

        .stat-card { padding: 20px; border-radius: 12px; background: #fff; box-shadow: var(--card-shadow); transition: transform 0.2s ease; border: 1px solid rgba(0,0,0,0.03); }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .stat-card .number { font-size: 1.8rem; font-weight: 700; color: #1a2332; line-height: 1.2; }
        .stat-card .label { color: #7f8c8d; font-size: 0.85rem; font-weight: 500; }

        @media (max-width: 992px) {
            .sidebar-wrapper { width: var(--sidebar-collapsed); }
            .sidebar-wrapper .sidebar-text { display: none; }
            .sidebar-wrapper .sidebar-logo h5,
            .sidebar-wrapper .sidebar-logo small { display: none; }
            .sidebar-wrapper .nav-link { justify-content: center; padding: 0.75rem 0; }
            .sidebar-wrapper .nav-link i { margin-right: 0; font-size: 1.3rem; }
            .sidebar-wrapper .nav-link .badge { display: none; }
            .sidebar-wrapper .menu-divider span { display: none; }
            .sidebar-wrapper .menu-divider { text-align: center; padding: 0.5rem 0; }
            .sidebar-wrapper .menu-divider::after { content: "···"; color: rgba(255,255,255,0.3); }
            .sidebar-wrapper .sidebar-toggle { display: none; }
            .sidebar-wrapper .collapse { display: none !important; }
            .main-content-wrapper { margin-left: var(--sidebar-collapsed); }
            .top-navbar { left: var(--sidebar-collapsed); }
        }

        @media (max-width: 768px) {
            .sidebar-wrapper { position: fixed; width: var(--sidebar-collapsed); height: 100%; transform: translateX(-100%); transition: transform var(--transition-speed) ease; z-index: 1060; }
            .sidebar-wrapper.mobile-open { transform: translateX(0); width: 280px; }
            .sidebar-wrapper.mobile-open .sidebar-text { display: inline; }
            .sidebar-wrapper.mobile-open .sidebar-logo h5 { display: block; }
            .sidebar-wrapper.mobile-open .sidebar-logo small { display: block; }
            .sidebar-wrapper.mobile-open .nav-link { justify-content: flex-start; padding: 0.65rem 1.2rem; }
            .sidebar-wrapper.mobile-open .nav-link i { margin-right: 12px; font-size: 1.15rem; }
            .sidebar-wrapper.mobile-open .nav-link .badge { display: inline; }
            .sidebar-wrapper.mobile-open .menu-divider span { display: inline; }
            .sidebar-wrapper.mobile-open .menu-divider::after { display: none; }
            .sidebar-wrapper.mobile-open .collapse { display: block !important; }
            .sidebar-wrapper.mobile-open .collapse:not(.show) { display: none !important; }
            .main-content-wrapper { margin-left: 0; }
            .top-navbar { left: 0; }
            .main-content { padding: 15px; }
            .mobile-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1055; display: none; }
            .mobile-overlay.show { display: block; }
        }

        .sidebar-wrapper::-webkit-scrollbar { width: 4px; }
        .sidebar-wrapper::-webkit-scrollbar-track { background: transparent; }
        .sidebar-wrapper::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
    </style>

    @stack('styles')
</head>
<body>

    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileSidebar()"></div>

    {{-- ============================================================
         SIDEBAR - MENU LATERAL DINÂMICO
         ============================================================ --}}
    <div class="sidebar-wrapper" id="sidebarWrapper">
        <div class="sidebar">

            <div class="sidebar-logo">
                <h5>🎯 Shotgun <span>MOZ</span></h5>
                <small>Painel Administrativo</small>
            </div>

            <button class="sidebar-toggle" id="sidebarToggle" title="Recolher/Expandir">
                <i class="bi bi-chevron-left" id="toggleIcon"></i>
            </button>

            <ul class="nav flex-column">

                {{-- PRINCIPAL (TODOS) --}}
                <li class="menu-divider"><span>Principal</span></li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                {{-- Locais (Apenas Admin/Operador) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isOperador())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('locais.*') ? 'active' : '' }}" href="{{ route('locais.index') }}">
                            <i class="bi bi-geo-alt"></i>
                            <span class="sidebar-text">Locais</span>
                        </a>
                    </li>
                @endif

                {{-- Eventos (Todos vêem, mas filtrados no controller) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('eventos.*') ? 'active' : '' }}" href="{{ route('eventos.index') }}">
                        <i class="bi bi-calendar-event"></i>
                        <span class="sidebar-text">Eventos</span>
                    </a>
                </li>

                {{-- INGRESSOS --}}
                <li class="menu-divider"><span>Ingressos</span></li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ingressos.*') ? 'active' : '' }}" href="{{ route('ingressos.geral') }}">
                        <i class="bi bi-ticket"></i>
                        <span class="sidebar-text">Ingressos</span>
                    </a>
                </li>

                {{-- Check-ins (Operador faz o check-in, Promoter não) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isOperador())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('checkins.*') ? 'active' : '' }}" href="{{ route('checkins.index') }}">
                            <i class="bi bi-check2-circle"></i>
                            <span class="sidebar-text">Check-ins</span>
                        </a>
                    </li>
                @endif

                {{-- Vendas, Pedidos e Pagamentos (Promoter vê os dele, Operador vê todos) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendas.*') ? 'active' : '' }}" href="{{ route('vendas.index') }}">
                        <i class="bi bi-cart"></i>
                        <span class="sidebar-text">Vendas</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}" href="{{ route('pedidos.index') }}">
                        <i class="bi bi-receipt"></i>
                        <span class="sidebar-text">Pedidos</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pagamentos.*') ? 'active' : '' }}" href="{{ route('pagamentos.index') }}">
                        <i class="bi bi-credit-card"></i>
                        <span class="sidebar-text">Pagamentos</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('carteira.*') ? 'active' : '' }}" href="{{ route('carteira.index') }}">
                        <i class="bi bi-wallet2"></i>
                        <span class="sidebar-text">Carteira</span>
                    </a>
                </li>

                {{-- GESTÃO FINANCEIRA (Apenas Admin e Operador) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isOperador())
                    <li class="menu-divider"><span>Gestão Financeira</span></li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('promoters.*') ? 'active' : '' }}" href="{{ route('promoters.index') }}">
                            <i class="bi bi-people"></i>
                            <span class="sidebar-text">Promoters</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('descontos.*') ? 'active' : '' }}" href="{{ route('descontos.index') }}">
                            <i class="bi bi-ticket-perforated"></i>
                            <span class="sidebar-text">Descontos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}" href="{{ route('relatorios.index') }}">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span class="sidebar-text">Relatórios</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.reembolsos*') ? 'active' : '' }}" href="{{ route('admin.reembolsos') }}">
                            <i class="bi bi-arrow-return-left"></i>
                            <span class="sidebar-text">Reembolsos</span>
                        </a>
                    </li>
                @endif

                {{-- ADMINISTRAÇÃO (Apenas Admin) --}}
                @if(auth()->user()->isAdmin())
                    <li class="menu-divider"><span>Administração</span></li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-people"></i>
                            <span class="sidebar-text">Usuários</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('organizacoes.*') ? 'active' : '' }}" href="{{ route('organizacoes.index') }}">
                            <i class="bi bi-building"></i>
                            <span class="sidebar-text">Organizações</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}" href="{{ route('auditoria.index') }}">
                            <i class="bi bi-clipboard-data"></i>
                            <span class="sidebar-text">Auditoria</span>
                        </a>
                    </li>
                @endif

                {{-- COMUNICAÇÃO (Admin e Operador) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isOperador())
                    <li class="menu-divider"><span>Comunicação</span></li>
                    {{-- SMS --}}
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('sms.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#submenuSms" role="button">
                            <span class="d-flex align-items-center gap-2"><i class="bi bi-chat-dots"></i><span class="sidebar-text">SMS</span></span>
                            <i class="bi bi-chevron-down sidebar-text" style="font-size: 0.7rem;"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('sms.*') ? 'show' : '' }}" id="submenuSms">
                            <ul class="nav flex-column ms-3 ps-2" style="border-left: 2px solid rgba(255,255,255,0.08);">
                                <li class="nav-item"><a class="nav-link" href="{{ route('sms.index') }}">Enviados</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('sms.templates.index') }}">Templates</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('sms.massa.index') }}">Envio em Massa</a></li>
                            </ul>
                        </div>
                    </li>
                    {{-- WhatsApp --}}
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#submenuWhatsapp" role="button">
                            <span class="d-flex align-items-center gap-2"><i class="bi bi-whatsapp"></i><span class="sidebar-text">WhatsApp</span></span>
                            <i class="bi bi-chevron-down sidebar-text" style="font-size: 0.7rem;"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('whatsapp.*') ? 'show' : '' }}" id="submenuWhatsapp">
                            <ul class="nav flex-column ms-3 ps-2" style="border-left: 2px solid rgba(255,255,255,0.08);">
                                <li class="nav-item"><a class="nav-link" href="{{ route('whatsapp.index') }}">Enviados</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('whatsapp.templates.index') }}">Templates</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('whatsapp.massa.index') }}">Envio em Massa</a></li>
                            </ul>
                        </div>
                    </li>
                @endif

                <hr class="menu-separator">

                {{-- PERFIL E SAIR --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person"></i>
                        <span class="sidebar-text">Perfil</span>
                    </a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start" style="color: rgba(255,255,255,0.7);">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="sidebar-text">Sair</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    {{-- ============================================================
         NAVBAR - BARRA SUPERIOR
         ============================================================ --}}
    <nav class="top-navbar" id="topNavbar">
        <div class="navbar-left">
            <button class="btn btn-link d-md-none p-0" onclick="toggleMobileSidebar()" style="color: #1a2332; font-size: 1.5rem;">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="page-title mb-0" id="pageTitle">@yield('title', 'Dashboard')</h6>
        </div>

        <div class="navbar-right">
            <a href="{{ route('notificacoes.index') }}" class="nav-icon" title="Notificações">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger" id="navbar-notificacoes-badge">0</span>
            </a>

            <div class="dropdown user-dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="user-avatar">{{ auth()->user()->name[0] ?? 'U' }}</div>
                    <span class="ms-2 d-none d-sm-inline" style="color: #1a2332; font-weight: 500; font-size: 0.85rem;">
                        {{ auth()->user()->name ?? 'Usuário' }}
                        <br>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ auth()->user()->role_name ?? '' }}</small>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Sair</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- ============================================================
         CONTEÚDO PRINCIPAL
         ============================================================ --}}
    <div class="main-content-wrapper" id="mainContentWrapper">
        <div class="main-content">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    {{-- ============================================================
         SCRIPTS
         ============================================================ --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const sidebarWrapper = document.getElementById('sidebarWrapper');
        const mainContentWrapper = document.getElementById('mainContentWrapper');
        const topNavbar = document.getElementById('topNavbar');
        const toggleIcon = document.getElementById('toggleIcon');
        const sidebarToggle = document.getElementById('sidebarToggle');

        let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebarWrapper.classList.add('collapsed');
            mainContentWrapper.classList.add('expanded');
            topNavbar.classList.add('expanded');
            toggleIcon.classList.remove('bi-chevron-left');
            toggleIcon.classList.add('bi-chevron-right');
        }

        sidebarToggle.addEventListener('click', function() {
            isCollapsed = !isCollapsed;
            if (isCollapsed) {
                sidebarWrapper.classList.add('collapsed');
                mainContentWrapper.classList.add('expanded');
                topNavbar.classList.add('expanded');
                toggleIcon.classList.remove('bi-chevron-left');
                toggleIcon.classList.add('bi-chevron-right');
            } else {
                sidebarWrapper.classList.remove('collapsed');
                mainContentWrapper.classList.remove('expanded');
                topNavbar.classList.remove('expanded');
                toggleIcon.classList.remove('bi-chevron-right');
                toggleIcon.classList.add('bi-chevron-left');
            }
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        function toggleMobileSidebar() {
            sidebarWrapper.classList.toggle('mobile-open');
            document.getElementById('mobileOverlay').classList.toggle('show');
            document.body.style.overflow = sidebarWrapper.classList.contains('mobile-open') ? 'hidden' : '';
        }

        function closeMobileSidebar() {
            sidebarWrapper.classList.remove('mobile-open');
            document.getElementById('mobileOverlay').classList.remove('show');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebarWrapper.contains(event.target);
            const isClickOnHamburger = event.target.closest('.btn-link');
            if (!isClickInsideSidebar && !isClickOnHamburger && window.innerWidth <= 768) {
                closeMobileSidebar();
            }
        });

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Tem certeza?',
                text: `Deseja realmente excluir "${name}"? Esta ação não pode ser desfeita.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const closeBtn = alert.querySelector('.btn-close');
                    if (closeBtn) closeBtn.click();
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>