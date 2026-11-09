<?php

namespace App\Http\Controllers;

use App\Models\CarteiraIngresso;
use App\Models\Ingresso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CarteiraController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista os ingressos da carteira do usuário
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Buscar ingressos da carteira do usuário
        $query = CarteiraIngresso::where('user_id', $user->id)
                                 ->with(['ingresso', 'ingresso.evento', 'ingresso.categoria']);

        // Filtro por status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Busca por texto
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('codigo_acesso', 'like', "%{$search}%")
                  ->orWhereHas('ingresso', function($ing) use ($search) {
                      $ing->where('qr_code', 'like', "%{$search}%")
                          ->orWhereHas('evento', function($ev) use ($search) {
                              $ev->where('nome', 'like', "%{$search}%");
                          });
                  });
            });
        }

        // Paginar resultados
        $carteira = $query->orderBy('created_at', 'desc')->paginate(12);

        // Estatísticas
        $total = CarteiraIngresso::where('user_id', $user->id)->count();
        $ativos = CarteiraIngresso::where('user_id', $user->id)
                                  ->where('status', CarteiraIngresso::STATUS_ATIVO)
                                  ->count();
        $utilizados = CarteiraIngresso::where('user_id', $user->id)
                                      ->where('status', CarteiraIngresso::STATUS_UTILIZADO)
                                      ->count();
        $transferidos = CarteiraIngresso::where('user_id', $user->id)
                                        ->where('status', CarteiraIngresso::STATUS_TRANSFERIDO)
                                        ->count();

        return view('admin.carteira.index', compact(
            'carteira',
            'total',
            'ativos',
            'utilizados',
            'transferidos'
        ));
    }

    /**
     * ============================================================
     * STORE: Adiciona um ingresso à carteira do usuário
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'ingresso_id' => 'required|exists:ingressos,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $ingresso = Ingresso::findOrFail($request->ingresso_id);
        $userId = $request->user_id ?? Auth::id();

        // Verificar se o ingresso já está na carteira
        $exists = CarteiraIngresso::where('ingresso_id', $ingresso->id)
                                  ->where('user_id', $userId)
                                  ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Ingresso já está na carteira deste usuário.');
        }

        // Adicionar à carteira
        $carteira = CarteiraIngresso::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'ingresso_id' => $ingresso->id,
            'status' => CarteiraIngresso::STATUS_ATIVO,
            'data_ativacao' => now(),
        ]);

        return redirect()->route('carteira.index')
            ->with('success', 'Ingresso adicionado à carteira com sucesso!');
    }

    /**
     * ============================================================
     * SHOW: Exibe detalhes de um ingresso na carteira
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $item = CarteiraIngresso::where('user_id', Auth::id())
                                ->with(['ingresso', 'ingresso.evento', 'ingresso.categoria', 'ingresso.checkin'])
                                ->findOrFail($id);

        return view('admin.carteira.show', compact('item'));
    }

    /**
     * ============================================================
     * QRCODE: Gera QR Code para o ingresso
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function qrcode($id)
    {
        $item = CarteiraIngresso::where('user_id', Auth::id())
                                ->with(['ingresso', 'ingresso.evento'])
                                ->findOrFail($id);

        // Gerar QR Code usando o código de acesso
        $qrCode = QrCode::size(300)
                        ->format('svg')
                        ->generate($item->codigo_acesso);

        return view('admin.carteira.qrcode', compact('item', 'qrCode'));
    }

    /**
     * ============================================================
     * DESTROY: Remove um ingresso da carteira
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $item = CarteiraIngresso::where('user_id', Auth::id())->findOrFail($id);

        // Verificar se pode remover (apenas se estiver ativo ou transferido)
        if ($item->isUtilizado()) {
            return redirect()->back()
                ->with('error', 'Não é possível remover um ingresso já utilizado.');
        }

        $item->delete();

        return redirect()->route('carteira.index')
            ->with('success', 'Ingresso removido da carteira.');
    }

    /**
     * ============================================================
     * ADICIONAR AUTOMATICAMENTE APÓS COMPRA
     * ============================================================
     * 
     * Este método deve ser chamado após a venda de um ingresso
     * para adicioná-lo automaticamente à carteira do participante.
     * 
     * @param Ingresso $ingresso
     * @param int $userId
     * @return void
     */
    public static function adicionarAposCompra($ingresso, $userId)
    {
        // Verificar se o ingresso já está na carteira
        $exists = CarteiraIngresso::where('ingresso_id', $ingresso->id)
                                  ->where('user_id', $userId)
                                  ->exists();

        if ($exists) {
            return;
        }

        CarteiraIngresso::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'ingresso_id' => $ingresso->id,
            'status' => CarteiraIngresso::STATUS_ATIVO,
            'data_ativacao' => now(),
        ]);
    }

    /**
     * ============================================================
     * VERIFICAR SE USER TEM INGRESSO NA CARTEIRA
     * ============================================================
     * 
     * @param int $userId
     * @param int $ingressoId
     * @return bool
     */
    public static function temIngressoNaCarteira($userId, $ingressoId): bool
    {
        return CarteiraIngresso::where('user_id', $userId)
                               ->where('ingresso_id', $ingressoId)
                               ->exists();
    }
}