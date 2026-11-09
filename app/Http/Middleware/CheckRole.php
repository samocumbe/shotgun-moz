<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Verifica se o utilizador tem o papel necessário para aceder à rota.
     * 
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles (ex: 'admin', 'admin,operador')
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Verificar se está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Se não houver restrição de papéis (passa direto)
        if (empty($roles)) {
            return $next($request);
        }

        // 3. Admin tem acesso a tudo
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 4. Se for CLIENTE a tentar entrar no painel admin, manda para a carteira
        if ($user->isCliente()) {
            return redirect()->route('carteira.index')
                ->with('info', 'Bem-vindo à tua área de cliente.');
        }

        // 5. Verificar se o papel está na lista permitida
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // 6. Bloquear acesso não autorizado
        abort(403, 'Acesso negado. Não tens permissão para acessar esta página.');
    }
}