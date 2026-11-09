<?php

/**
 * ============================================================
 * CONTROLLER: RelatorioController
 * ============================================================
 * 
 * Gerencia a geração de relatórios do sistema:
 * - Relatório de Vendas (PDF)
 * - Relatório de Check-ins (PDF)
 * - Relatório Financeiro (PDF)
 * - Relatório de Promoters (PDF)
 * - Relatório Detalhado de Promoter (PDF)
 * 
 * Todos os relatórios podem ser exportados em PDF.
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Venda;
use App\Models\Checkin;
use App\Models\Promoter;
use App\Models\Comissao;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    /**
     * ============================================================
     * INDEX: Página principal de relatórios
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Buscar eventos publicados para exibir nos cards
        $eventos = Evento::where('status', 'publicado')->get();
        $promoters = Promoter::where('status', 'ativo')->get();
        
        return view('admin.relatorios.index', compact('eventos', 'promoters'));
    }

    /**
     * ============================================================
     * VENDAS: Relatório de vendas do evento
     * ============================================================
     * 
     * @param Request $request
     * @param int $eventoId
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function vendas(Request $request, $eventoId)
    {
        // Se for 0, mostra página de seleção de evento
        if ($eventoId == 0) {
            $eventos = Evento::where('status', 'publicado')->get();
            return view('admin.relatorios.vendas-selecao', compact('eventos'));
        }

        // Buscar evento
        $evento = Evento::findOrFail($eventoId);
        
        // Construir query de vendas
        $query = Venda::where('evento_id', $eventoId)
                      ->with(['participante', 'user', 'pedido']);
        
        // Aplicar filtros
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('data_venda', '>=', $request->data_inicio);
        }
        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('data_venda', '<=', $request->data_fim);
        }
        if ($request->has('tipo') && $request->tipo) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Executar query
        $vendas = $query->orderBy('data_venda', 'desc')->get();
        
        // Calcular totais
        $totais = [
            'total_vendas' => $vendas->count(),
            'valor_total' => $vendas->sum('valor_total'),
            'valor_medio' => $vendas->count() > 0 ? $vendas->sum('valor_total') / $vendas->count() : 0,
            'por_tipo' => $vendas->groupBy('tipo')->map(function($item) {
                return [
                    'quantidade' => $item->count(),
                    'valor' => $item->sum('valor_total'),
                ];
            }),
            'por_status' => $vendas->groupBy('status')->map(function($item) {
                return $item->count();
            }),
        ];
        
        // Exportar PDF se solicitado
        if ($request->has('exportar') && $request->exportar === 'pdf') {
            $pdf = Pdf::loadView('admin.relatorios.pdf.vendas', compact('evento', 'vendas', 'totais'));
            return $pdf->download("relatorio-vendas-{$evento->id}.pdf");
        }
        
        // Mostrar detalhes
        return view('admin.relatorios.vendas-detalhes', compact('evento', 'vendas', 'totais'));
    }

    /**
     * ============================================================
     * CHECKINS: Relatório de check-ins do evento
     * ============================================================
     * 
     * @param Request $request
     * @param int $eventoId
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function checkins(Request $request, $eventoId)
    {
        // Se for 0, mostra página de seleção de evento
        if ($eventoId == 0) {
            $eventos = Evento::where('status', 'publicado')->get();
            return view('admin.relatorios.checkins-selecao', compact('eventos'));
        }

        // Buscar evento
        $evento = Evento::findOrFail($eventoId);
        
        // Construir query de check-ins
        $query = Checkin::where('evento_id', $eventoId)
                        ->with(['ingresso', 'ingresso.participante', 'dispositivo']);
        
        // Aplicar filtros
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('data_checkin', '>=', $request->data_inicio);
        }
        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('data_checkin', '<=', $request->data_fim);
        }
        if ($request->has('porta_acesso') && $request->porta_acesso) {
            $query->where('porta_acesso', $request->porta_acesso);
        }
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Executar query
        $checkins = $query->orderBy('data_checkin', 'desc')->get();
        
        // Calcular totais
        $totais = [
            'total_checkins' => $checkins->count(),
            'por_porta' => $checkins->groupBy('porta_acesso')->map(function($item) {
                return $item->count();
            }),
            'por_status' => $checkins->groupBy('status')->map(function($item) {
                return $item->count();
            }),
            'por_hora' => $checkins->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->data_checkin)->format('H:00');
            })->map(function($item) {
                return $item->count();
            }),
        ];
        
        // Exportar PDF se solicitado
        if ($request->has('exportar') && $request->exportar === 'pdf') {
            $pdf = Pdf::loadView('admin.relatorios.pdf.checkins', compact('evento', 'checkins', 'totais'));
            return $pdf->download("relatorio-checkins-{$evento->id}.pdf");
        }
        
        // Mostrar detalhes
        return view('admin.relatorios.checkins-detalhes', compact('evento', 'checkins', 'totais'));
    }

    /**
     * ============================================================
     * FINANCEIRO: Relatório financeiro do evento
     * ============================================================
     * 
     * @param Request $request
     * @param int $eventoId
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function financeiro(Request $request, $eventoId)
    {
        // Se for 0, mostra página de seleção de evento
        if ($eventoId == 0) {
            $eventos = Evento::where('status', 'publicado')->get();
            return view('admin.relatorios.financeiro-selecao', compact('eventos'));
        }

        // Buscar evento
        $evento = Evento::findOrFail($eventoId);
        
        // Buscar vendas confirmadas
        $vendas = Venda::where('evento_id', $eventoId)
                       ->where('status', 'confirmada')
                       ->get();
        
        // Calcular receita
        $receita_total = $vendas->sum('valor_total');
        
        // Buscar pagamentos por método
        $pagamentos = \App\Models\Pagamento::whereHas('pedido', function($q) use ($eventoId) {
            $q->where('evento_id', $eventoId);
        })->get();
        
        $por_metodo = $pagamentos->groupBy('metodo')->map(function($item) {
            return $item->sum('valor');
        });
        
        // Buscar comissões
        $comissoes = Comissao::whereHas('venda', function($q) use ($eventoId) {
            $q->where('evento_id', $eventoId);
        })->get();
        
        $total_comissoes = $comissoes->sum('valor_comissao');
        $comissoes_pagas = $comissoes->where('status', 'pago')->sum('valor_comissao');
        $comissoes_pendentes = $comissoes->where('status', 'pendente')->sum('valor_comissao');
        
        // Montar resumo
        $resumo = [
            'receita_bruta' => $receita_total,
            'total_comissoes' => $total_comissoes,
            'receita_liquida' => $receita_total - $total_comissoes,
            'comissoes_pagas' => $comissoes_pagas,
            'comissoes_pendentes' => $comissoes_pendentes,
            'total_vendas' => $vendas->count(),
            'valor_medio' => $vendas->count() > 0 ? $receita_total / $vendas->count() : 0,
            'por_metodo' => $por_metodo,
        ];
        
        // Exportar PDF se solicitado
        if ($request->has('exportar') && $request->exportar === 'pdf') {
            $pdf = Pdf::loadView('admin.relatorios.pdf.financeiro', compact('evento', 'resumo'));
            return $pdf->download("relatorio-financeiro-{$evento->id}.pdf");
        }
        
        // Mostrar detalhes
        return view('admin.relatorios.financeiro-detalhes', compact('evento', 'resumo'));
    }

    /**
     * ============================================================
     * PROMOTERS: Relatório de desempenho dos promoters
     * ============================================================
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function promoters(Request $request)
    {
        // Buscar promoters
        $query = Promoter::with(['comissoes']);
        
        // Aplicar filtros
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }
        
        // Executar query
        $promoters = $query->get();
        
        // Montar estatísticas por promoter
        $estatisticas = $promoters->map(function($promoter) {
            return [
                'id' => $promoter->id,
                'nome' => $promoter->nome,
                'email' => $promoter->email,
                'codigo' => $promoter->codigo_promocional,
                'comissao_padrao' => $promoter->comissao_padrao,
                'total_vendas' => $promoter->total_vendas,
                'total_comissao' => $promoter->total_comissao,
                'comissoes_pagas' => $promoter->comissoes()->where('status', 'pago')->sum('valor_comissao'),
                'comissoes_pendentes' => $promoter->comissoes()->where('status', 'pendente')->sum('valor_comissao'),
                'status' => $promoter->status,
            ];
        });
        
        // Calcular totais gerais
        $totais = [
            'total_promoters' => $promoters->count(),
            'total_vendas' => $promoters->sum('total_vendas'),
            'total_comissoes' => $promoters->sum('total_comissao'),
            'ativos' => $promoters->where('status', 'ativo')->count(),
            'inativos' => $promoters->where('status', 'inativo')->count(),
            'pendentes' => $promoters->where('status', 'pendente')->count(),
        ];
        
        // Exportar PDF se solicitado
        if ($request->has('exportar') && $request->exportar === 'pdf') {
            $pdf = Pdf::loadView('admin.relatorios.pdf.promoters', compact('estatisticas', 'totais'));
            return $pdf->download("relatorio-promoters.pdf");
        }
        
        // Mostrar detalhes
        return view('admin.relatorios.promoters', compact('estatisticas', 'totais'));
    }

    /**
     * ============================================================
     * PROMOTER DETALHES: Relatório detalhado de um promoter
     * ============================================================
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function promoterDetalhes(Request $request, $id)
    {
        // Buscar promoter com todos os relacionamentos
        $promoter = Promoter::with(['comissoes', 'vendasPromoter.pedido', 'vendasPromoter.venda.evento'])
                            ->findOrFail($id);
        
        // Comissões do promoter
        $comissoes = $promoter->comissoes()
                              ->with(['venda', 'pedido'])
                              ->orderBy('created_at', 'desc')
                              ->get();
        
        // Resumo financeiro do promoter
        $resumo = [
            'total_vendas' => $promoter->vendasPromoter()->count(),
            'valor_total' => $promoter->vendasPromoter()->sum('valor_total'),
            'comissao_total' => $promoter->comissoes()->sum('valor_comissao'),
            'comissoes_pagas' => $promoter->comissoes()->where('status', 'pago')->sum('valor_comissao'),
            'comissoes_pendentes' => $promoter->comissoes()->where('status', 'pendente')->sum('valor_comissao'),
        ];
        
        // Vendas por evento (agrupadas)
        $vendas_por_evento = $promoter->vendasPromoter()
                                      ->with('venda.evento')
                                      ->get()
                                      ->groupBy('venda.evento_id')
                                      ->map(function($vendas) {
                                          return [
                                              'evento' => $vendas->first()->venda->evento->nome ?? 'N/A',
                                              'quantidade' => $vendas->count(),
                                              'valor' => $vendas->sum('valor_total'),
                                              'comissao' => $vendas->sum('comissao_gerada'),
                                          ];
                                      });
        
        // Exportar PDF se solicitado
        if ($request->has('exportar') && $request->exportar === 'pdf') {
            $pdf = Pdf::loadView('admin.relatorios.pdf.promoter-detalhes', compact('promoter', 'comissoes', 'resumo', 'vendas_por_evento'));
            return $pdf->download("relatorio-promoter-{$promoter->id}.pdf");
        }
        
        // Mostrar detalhes
        return view('admin.relatorios.promoter-detalhes', compact('promoter', 'comissoes', 'resumo', 'vendas_por_evento'));
    }
}