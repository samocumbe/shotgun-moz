<?php

/**
 * ============================================================
 * CONTROLLER: DispositivoController - SHOTGUN-MOZ
 * ============================================================
 *
 * Gere os dispositivos (scanners/POS/supervisores) do sistema.
 *
 * Comportamento:
 * - Pedidos WEB  → devolvem views Blade
 * - Pedidos JSON → devolvem JSON (compatibilidade com API)
 *                  (basta enviar o header Accept: application/json)
 *
 * Rotas (resource + extra):
 * - GET    /admin/dispositivos              → index
 * - GET    /admin/dispositivos/create       → create
 * - POST   /admin/dispositivos              → store
 * - GET    /admin/dispositivos/{id}         → show
 * - GET    /admin/dispositivos/{id}/edit    → edit
 * - PUT    /admin/dispositivos/{id}         → update
 * - DELETE /admin/dispositivos/{id}         → destroy
 * - POST   /admin/dispositivos/{id}/sincronizar → sincronizar
 *
 * Multi-utilizador:
 * - Admin vê todos os dispositivos
 * - Outros utilizadores veem apenas os seus
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Dispositivo;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DispositivoController extends Controller
{
    // ============================================================
    // INDEX — LISTAGEM COM FILTROS
    // ============================================================

    /**
     * Lista os dispositivos com filtros (nome, tipo, status, evento).
     * Devolve view (web) ou JSON (API).
     */
    public function index(Request $request)
    {
        $query = Dispositivo::with(['evento', 'user']);

        // Multi-utilizador: admin vê todos
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        // Filtros
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('evento_id')) {
            $query->where('evento_id', $request->evento_id);
        }

        $dispositivos = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15))
            ->withQueryString();

        // API (JSON)
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $dispositivos,
                'message' => 'Dispositivos listados com sucesso',
            ]);
        }

        // Web (view)
        $eventos = Evento::orderBy('nome')->get();

        return view('admin.dispositivos.index',
            compact('dispositivos', 'eventos'));
    }

    // ============================================================
    // CREATE — FORMULÁRIO
    // ============================================================

    /**
     * Formulário para criar um novo dispositivo.
     */
    public function create()
    {
        $eventos = Evento::orderBy('nome')->get();

        return view('admin.dispositivos.create', compact('eventos'));
    }

    // ============================================================
    // STORE — CRIAR
    // ============================================================

    /**
     * Cria um novo dispositivo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome'      => 'required|string|max:255',
            'tipo'      => ['required', Rule::in([
                Dispositivo::TIPO_SCANNER,
                Dispositivo::TIPO_POS,
                Dispositivo::TIPO_SUPERVISOR,
                Dispositivo::TIPO_ADMIN,
            ])],
            'evento_id' => 'nullable|exists:eventos,id',
            'status'    => ['required', Rule::in([
                Dispositivo::STATUS_ATIVO,
                Dispositivo::STATUS_INATIVO,
            ])],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                    'message' => 'Erro de validação',
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dispositivo = Dispositivo::create([
            'uuid'      => (string) Str::uuid(),
            'nome'      => $request->nome,
            'tipo'      => $request->tipo,
            'evento_id' => $request->evento_id,
            'user_id'   => auth()->id(),
            'status'    => $request->status,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $dispositivo,
                'message' => 'Dispositivo registado com sucesso',
            ], 201);
        }

        return redirect()->route('dispositivos.index')
            ->with('success', 'Dispositivo "' . $dispositivo->nome . '" criado com sucesso!');
    }

    // ============================================================
    // SHOW — DETALHES
    // ============================================================

    /**
     * Detalhes de um dispositivo com estatísticas.
     */
    public function show(Request $request, $id)
    {
        $query = Dispositivo::with(['evento', 'user']);

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $dispositivo = $query->find($id);

        if (!$dispositivo) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não encontrado',
                ], 404);
            }
            abort(404, 'Dispositivo não encontrado');
        }

        // Estatísticas
        $estatisticas = [
            'checkins_realizados' => $dispositivo->checkins()->count(),
            'checkins_pendentes'  => $dispositivo->checkins()->where('status', 'pendente')->count(),
            'operacoes_sync'      => $dispositivo->syncQueues()->count(),
            'ultimo_sync'         => $dispositivo->ultimo_sync,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'dispositivo'  => $dispositivo,
                    'estatisticas' => $estatisticas,
                ],
                'message' => 'Dispositivo encontrado com sucesso',
            ]);
        }

        return view('admin.dispositivos.show',
            compact('dispositivo', 'estatisticas'));
    }

    // ============================================================
    // EDIT — FORMULÁRIO
    // ============================================================

    /**
     * Formulário de edição.
     */
    public function edit($id)
    {
        $query = Dispositivo::query();

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $dispositivo = $query->findOrFail($id);
        $eventos = Evento::orderBy('nome')->get();

        return view('admin.dispositivos.edit',
            compact('dispositivo', 'eventos'));
    }

    // ============================================================
    // UPDATE — GUARDAR ALTERAÇÕES
    // ============================================================

    /**
     * Atualiza um dispositivo.
     */
    public function update(Request $request, $id)
    {
        $query = Dispositivo::query();

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $dispositivo = $query->find($id);

        if (!$dispositivo) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não encontrado',
                ], 404);
            }
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'nome'      => 'sometimes|string|max:255',
            'tipo'      => ['sometimes', Rule::in([
                Dispositivo::TIPO_SCANNER,
                Dispositivo::TIPO_POS,
                Dispositivo::TIPO_SUPERVISOR,
                Dispositivo::TIPO_ADMIN,
            ])],
            'evento_id' => 'nullable|exists:eventos,id',
            'status'    => ['sometimes', Rule::in([
                Dispositivo::STATUS_ATIVO,
                Dispositivo::STATUS_INATIVO,
            ])],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                    'message' => 'Erro de validação',
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dispositivo->update($request->only([
            'nome', 'tipo', 'evento_id', 'status',
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $dispositivo->fresh(['evento', 'user']),
                'message' => 'Dispositivo atualizado com sucesso',
            ]);
        }

        return redirect()->route('dispositivos.index')
            ->with('success', 'Dispositivo atualizado com sucesso!');
    }

    // ============================================================
    // DESTROY — ELIMINAR
    // ============================================================

    /**
     * Remove um dispositivo.
     * Bloqueia se houver operações de sync pendentes.
     */
    public function destroy(Request $request, $id)
    {
        $query = Dispositivo::query();

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $dispositivo = $query->find($id);

        if (!$dispositivo) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não encontrado',
                ], 404);
            }
            abort(404);
        }

        // Bloquear se houver sync pendente
        if ($dispositivo->syncQueues()->where('status', 'pendente')->exists()) {
            $msg = 'Dispositivo tem operações pendentes. Não pode ser eliminado.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $nome = $dispositivo->nome;
        $dispositivo->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dispositivo removido com sucesso',
            ]);
        }

        return redirect()->route('dispositivos.index')
            ->with('success', 'Dispositivo "' . $nome . '" removido com sucesso!');
    }

    // ============================================================
    // SINCRONIZAR — ATUALIZA TIMESTAMP
    // ============================================================

    /**
     * Atualiza o timestamp `ultimo_sync` do dispositivo.
     */
    public function sincronizar(Request $request, $id)
    {
        $query = Dispositivo::query();

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $dispositivo = $query->find($id);

        if (!$dispositivo) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não encontrado',
                ], 404);
            }
            abort(404);
        }

        $dispositivo->atualizarUltimoSync();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => ['ultimo_sync' => $dispositivo->ultimo_sync],
                'message' => 'Sincronização atualizada com sucesso',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Dispositivo sincronizado com sucesso!');
    }
}