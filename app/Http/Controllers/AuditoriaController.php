<?php

/**
 * ============================================================
 * CONTROLLER: AuditoriaController
 * ============================================================
 * 
 * Gerencia todas as operações relacionadas a auditoria:
 * - Listagem de logs com filtros
 * - Visualização de detalhes
 * - Exportação de logs
 * - Limpeza de logs antigos
 * 
 * Multi-Organizador:
 * - Organizadores veem apenas logs da sua organização
 * - Admin vê todos os logs
 * 
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use App\Models\Organizacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditoriaController extends Controller
{
    /**
     * ============================================================
     * INDEX: Lista todos os logs de auditoria
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     * 
     * Filtros disponíveis:
     * - user_id: Filtrar por usuário
     * - acao: create, update, delete, login, etc
     * - tabela: eventos, usuarios, vendas, etc
     * - data_inicio: Data inicial
     * - data_fim: Data final
     * - search: Busca por UUID ou ID do registro
     * 
     * 🔥 Multi-Organizador: Organizadores veem apenas logs da sua organização
     */
    public function index(Request $request)
    {
        $query = Auditoria::with(['user', 'organizacao']);

        // ============================================================
        // 🔥 MULTI-ORGANIZADOR: Filtrar por organização
        // ============================================================
        if (!Auth::user()->isAdmin()) {
            $query->where('organizacao_id', Auth::user()->organizacao_id);
        }

        // Filtro por usuário
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por ação
        if ($request->has('acao') && $request->acao) {
            $query->where('acao', $request->acao);
        }

        // Filtro por tabela
        if ($request->has('tabela') && $request->tabela) {
            $query->where('tabela', $request->tabela);
        }

        // Filtro por data
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // Busca por UUID ou ID do registro
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                  ->orWhere('registro_id', $search);
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(30);

        // 🔥 Dados para os filtros
        $usuarios = User::orderBy('name')->get();
        $acoes = ['create', 'update', 'delete', 'login', 'logout', 'import', 'export', 'publish', 'cancel', 'restore'];
        $tabelas = Auditoria::select('tabela')->distinct()->pluck('tabela');

        // Estatísticas
        $estatisticas = [
            'total' => Auditoria::count(),
            'criacoes' => Auditoria::where('acao', 'create')->count(),
            'atualizacoes' => Auditoria::where('acao', 'update')->count(),
            'exclusoes' => Auditoria::where('acao', 'delete')->count(),
            'hoje' => Auditoria::whereDate('created_at', today())->count(),
        ];

        return view('admin.auditoria.index', compact('logs', 'usuarios', 'acoes', 'tabelas', 'estatisticas'));
    }

    /**
     * ============================================================
     * SHOW: Exibe os detalhes de um log específico
     * ============================================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     * 
     * 🔥 Multi-Organizador: Verifica permissão antes de exibir
     */
    public function show($id)
    {
        $log = Auditoria::with(['user', 'organizacao'])
                        ->findOrFail($id);

        // ============================================================
        // 🔥 MULTI-ORGANIZADOR: Verificar permissão
        // ============================================================
        if (!Auth::user()->isAdmin() && Auth::user()->organizacao_id !== $log->organizacao_id) {
            abort(403, 'Você não tem permissão para visualizar este log.');
        }

        return view('admin.auditoria.show', compact('log'));
    }

    /**
     * ============================================================
     * LIMPAR: Remove logs antigos
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Remove logs com mais de X dias (padrão: 90)
     */
    public function limpar(Request $request)
    {
        try {
            $dias = $request->dias ?? 90;
            $dataLimite = now()->subDays($dias);

            $quantidade = Auditoria::where('created_at', '<', $dataLimite)->delete();

            Log::info('Logs antigos removidos: ' . $quantidade . ' registros por ' . Auth::user()->email);

            return response()->json([
                'success' => true,
                'message' => "✅ {$quantidade} logs removidos com sucesso!"
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao limpar logs: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * EXPORTAR: Exporta logs para CSV
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportar(Request $request)
    {
        $query = Auditoria::with(['user', 'organizacao']);

        if (!Auth::user()->isAdmin()) {
            $query->where('organizacao_id', Auth::user()->organizacao_id);
        }

        // Aplicar filtros
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('acao') && $request->acao) {
            $query->where('acao', $request->acao);
        }

        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Gerar CSV
        $filename = 'auditoria_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'UUID', 'Usuário', 'Organização', 'Ação', 'Tabela', 'Registro', 'Data', 'IP', 'Rota']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->uuid,
                    $log->user->name ?? 'Sistema',
                    $log->organizacao->nome ?? 'N/A',
                    $log->acao,
                    $log->tabela,
                    $log->registro_id,
                    $log->created_at->format('d/m/Y H:i:s'),
                    $log->ip ?? 'N/A',
                    $log->rota ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}