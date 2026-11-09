<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Ingresso;
use App\Models\Checkin;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ============================================================
        // 1. LÓGICA DE FILTRAGEM POR PAPEL (RBAC)
        // ============================================================
        
        if ($user->isAdmin()) {
            // ADMIN: Vê tudo globalmente
            $queryIngressos = Ingresso::where('status', 'pago');
            $queryCheckins  = Checkin::query();
            $queryVendas    = Venda::query();
            $queryEventos   = Evento::query();

        } elseif ($user->isOperador()) {
            // OPERADOR: Vê tudo da sua organização
            $queryIngressos = Ingresso::whereHas('evento', function($q) use ($user) {
                $q->where('organizacao_id', $user->organizacao_id);
            })->where('status', 'pago');
            
            $queryCheckins = Checkin::whereHas('evento', function($q) use ($user) {
                $q->where('organizacao_id', $user->organizacao_id);
            });
            
            $queryVendas  = Venda::whereHas('evento', function($q) use ($user) {
                $q->where('organizacao_id', $user->organizacao_id);
            });
            
            $queryEventos = Evento::where('organizacao_id', $user->organizacao_id);

        } elseif ($user->isPromoter()) {
            // PROMOTER: Vê apenas o que ele próprio vendeu
            // Usamos a tabela vendas diretamente para evitar depender de relações no Participante
            $queryIngressos = Ingresso::whereIn('participante_id', function($query) use ($user) {
                $query->select('participante_id')
                      ->from('vendas')
                      ->where('user_id', $user->id);
            })->where('status', 'pago');
            
            $queryCheckins = Checkin::whereHas('ingresso', function($q) use ($user) {
                $q->whereIn('participante_id', function($sub) use ($user) {
                    $sub->select('participante_id')->from('vendas')->where('user_id', $user->id);
                });
            });
            
            $queryVendas  = Venda::where('user_id', $user->id);
            
            // Promoter vê os eventos da organização dele
            $queryEventos = Evento::where('organizacao_id', $user->organizacao_id);

        } else {
            // CLIENTE (Segurança extra): Vê apenas o que comprou
            $email = $user->email;
            
            $queryIngressos = Ingresso::whereHas('participante', function($q) use ($email) {
                $q->where('email', $email);
            })->where('status', 'pago');
            
            $queryCheckins = Checkin::whereHas('ingresso.participante', function($q) use ($email) {
                $q->where('email', $email);
            });
            
            $queryVendas  = Venda::whereHas('participante', function($q) use ($email) {
                $q->where('email', $email);
            });
            
            $queryEventos = Evento::where('status', 'publicado')->where('data_fim', '>=', now());
        }

        // ============================================================
        // 2. ESTATÍSTICAS GERAIS (Filtradas)
        // ============================================================
        $eventos_ativos = (clone $queryEventos)->where('status', 'publicado')->where('data_fim', '>=', now())->count();
        $eventos_ativos_mes = (clone $queryEventos)->where('status', 'publicado')->whereMonth('created_at', now()->month)->count();
        
        $ingressos_vendidos = (clone $queryIngressos)->count();
        $ingressos_hoje = (clone $queryIngressos)->whereDate('data_compra', now()->toDateString())->count();
        
        $checkins_hoje = (clone $queryCheckins)->whereDate('data_checkin', now()->toDateString())->count();
        
        // REGRA: Promoter não vê receita. Admin e Operador vêem.
        $receita_total = $user->isPromoter() ? 0 : (clone $queryIngressos)->sum('preco_pago');
        $receita_mes = $user->isPromoter() ? 0 : (clone $queryIngressos)->whereMonth('data_compra', now()->month)->sum('preco_pago');

        // ============================================================
        // 3. LISTAS RECENTES E GRÁFICOS
        // ============================================================
        $eventos_recentes = (clone $queryEventos)->with('local')->orderBy('created_at', 'desc')->limit(5)->get();
        
        $checkins_recentes = (clone $queryCheckins)->with(['ingresso.participante', 'evento'])
                                    ->orderBy('data_checkin', 'desc')
                                    ->limit(5)
                                    ->get();

        $vendas_labels = [];
        $vendas_dados = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $data = now()->subDays($i);
            $vendas_labels[] = $data->format('d/m');
            $vendas_dados[] = (clone $queryIngressos)->whereDate('data_compra', $data->toDateString())->count();
        }
        
        $distribuicao_labels = ['Normal', 'VIP', 'Backstage', 'Meia', 'Estudante'];
        $distribuicao_dados = [];
        foreach ($distribuicao_labels as $cat) {
            $catSlug = strtolower($cat);
            $distribuicao_dados[] = (clone $queryIngressos)->whereHas('categoria', function($q) use ($catSlug) {
                $q->where('tipo', $catSlug);
            })->count();
        }

        return view('admin.dashboard', compact(
            'eventos_ativos', 'eventos_ativos_mes', 'ingressos_vendidos',
            'ingressos_hoje', 'checkins_hoje', 'receita_total', 'receita_mes',
            'eventos_recentes', 'checkins_recentes', 'vendas_labels',
            'vendas_dados', 'distribuicao_labels', 'distribuicao_dados'
        ));
    }
}