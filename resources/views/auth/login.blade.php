{{--
    ============================================================
    VIEW: Login - SHOTGUN-MOZ
    ============================================================
    Tela de login redesenhada com a identidade do SHOTGUN-MOZ.
    Layout em duas colunas:
    - Esquerda: branding + benefícios (desktop)
    - Direita: formulário

    Em mobile, a coluna esquerda desaparece.
--}}

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Entrar - SHOTGUN-MOZ</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand: #1a73e8;
            --brand-dark: #0d47a1;
        }
        body {
            font-family: 'Figtree', system-ui, sans-serif;
        }

        /* Painel esquerdo (branding) */
        .brand-panel {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            position: relative;
            overflow: hidden;
            color: #fff;
        }
        .brand-panel::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -30%;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .brand-panel .content {
            position: relative;
            z-index: 2;
        }

        /* Formulário */
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: #fff;
            color: #0f172a;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
        }
        .form-input.is-invalid {
            border-color: #ef4444;
        }

        .btn-primary-custom {
            width: 100%;
            background: var(--brand);
            color: #fff;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-primary-custom:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(26,115,232,0.3);
        }

        .link-brand {
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
        }
        .link-brand:hover {
            text-decoration: underline;
        }

        .error-msg {
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
    </style>
</head>
<body class="antialiased bg-gray-50">

<div class="min-h-screen flex">

    {{-- ============================================================
         PAINEL ESQUERDO — BRANDING (visível só em desktop)
         ============================================================ --}}
    <div class="hidden lg:flex lg:w-1/2 brand-panel">
        <div class="content flex flex-col justify-between p-12 w-full">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-white no-underline">
                <span style="font-size: 1.75rem;">🎯</span>
                <span style="font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px;">
                    Shotgun <span style="color: #4fc3f7;">MOZ</span>
                </span>
            </a>

            {{-- Conteúdo central --}}
            <div>
                <h1 style="font-size: 2.5rem; font-weight: 800; line-height: 1.15; margin-bottom: 1.5rem; letter-spacing: -1px;">
                    Bem-vindo de volta.
                </h1>
                <p style="font-size: 1.125rem; opacity: 0.9; margin-bottom: 2.5rem; max-width: 28rem;">
                    Entra na tua conta para acederes aos teus ingressos,
                    compras e eventos favoritos.
                </p>

                {{-- Benefícios --}}
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 2.5rem; height: 2.5rem; background: rgba(255,255,255,0.2); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-ticket-perforated" style="font-size: 1.1rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700;">Os teus ingressos num só lugar</div>
                            <div style="font-size: 0.875rem; opacity: 0.8;">Carteira digital sempre acessível</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 2.5rem; height: 2.5rem; background: rgba(255,255,255,0.2); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-qr-code" style="font-size: 1.1rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700;">Check-in sem filas</div>
                            <div style="font-size: 0.875rem; opacity: 0.8;">QR code direto no telemóvel</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 2.5rem; height: 2.5rem; background: rgba(255,255,255,0.2); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-shield-check" style="font-size: 1.1rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700;">Pagamento 100% seguro</div>
                            <div style="font-size: 0.875rem; opacity: 0.8;">M-Pesa, e-Mola e cartão</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rodapé --}}
            <div style="font-size: 0.8rem; opacity: 0.7;">
                © {{ date('Y') }} SHOTGUN-MOZ · Todos os direitos reservados
            </div>

        </div>
    </div>

    {{-- ============================================================
         PAINEL DIREITO — FORMULÁRIO
         ============================================================ --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">

            {{-- Logo mobile --}}
            <div class="lg:hidden mb-8 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-gray-900 no-underline">
                    <span style="font-size: 1.5rem;">🎯</span>
                    <span style="font-size: 1.25rem; font-weight: 800; letter-spacing: -0.5px;">
                        Shotgun <span style="color: #1a73e8;">MOZ</span>
                    </span>
                </a>
            </div>

            {{-- Título --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Entrar na conta</h2>
                <p class="text-sm text-gray-600">
                    Ainda não tens conta?
                    <a href="{{ route('register') }}" class="link-brand">Criar conta grátis</a>
                </p>
            </div>

            {{-- Erros gerais --}}
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <i class="bi bi-exclamation-triangle"></i>
                    Verifica os campos abaixo.
                </div>
            @endif

            {{-- Sessão expirada (419) --}}
            @if (session('status'))
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                    <i class="bi bi-info-circle"></i> {{ session('status') }}
                </div>
            @endif

            {{-- Formulário --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="o.teu@email.com"
                        class="form-input @error('email') is-invalid @enderror"
                    >
                    @error('email')
                        <div class="error-msg">
                            <i class="bi bi-x-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Palavra-passe
                    </label>
                    <div style="position: relative;">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="form-input @error('password') is-invalid @enderror"
                            style="padding-right: 3rem;"
                        >
                        <button type="button"
                                onclick="togglePassword('password', this)"
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-msg">
                            <i class="bi bi-x-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Lembrar-me + Esqueceste --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>Lembrar-me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link-brand text-sm">
                            Esqueceste a palavra-passe?
                        </a>
                    @endif
                </div>

                {{-- Botão --}}
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>

            {{-- Link para registo (mobile) --}}
            <p class="mt-8 text-center text-sm text-gray-600 lg:hidden">
                Ainda não tens conta?
                <a href="{{ route('register') }}" class="link-brand">Criar conta grátis</a>
            </p>

            {{-- Footer minimal --}}
            <p class="mt-8 text-center text-xs text-gray-400">
                © {{ date('Y') }} SHOTGUN-MOZ
            </p>

        </div>
    </div>

</div>

<script>
    // Toggle mostrar/ocultar palavra-passe
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon  = btn.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>

</body>
</html>