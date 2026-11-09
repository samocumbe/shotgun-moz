<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    public function index()
    {
        $notificacoes = Notificacao::where('user_id', Auth::id())
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(15);
        return view('admin.notificacoes.index', compact('notificacoes'));
    }

    public function marcarLida($id)
    {
        $notificacao = Notificacao::where('user_id', Auth::id())
                                   ->findOrFail($id);
        $notificacao->marcarComoLida();

        return redirect()->back()->with('success', 'Notificação marcada como lida!');
    }

    public function marcarTodasLidas()
    {
        Notificacao::where('user_id', Auth::id())
                   ->where('lida', false)
                   ->update([
                       'lida' => true,
                       'data_leitura' => now(),
                   ]);

        return redirect()->back()->with('success', 'Todas as notificações marcadas como lidas!');
    }

    public function destroy($id)
    {
        $notificacao = Notificacao::where('user_id', Auth::id())
                                   ->findOrFail($id);
        $notificacao->delete();

        return redirect()->back()->with('success', 'Notificação removida!');
    }
}