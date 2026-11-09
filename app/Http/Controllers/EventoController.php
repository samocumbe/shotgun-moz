<?php

/**
 * ============================================================
 * CONTROLLER: EventoController
 * ============================================================
 * 
 * Regras de Acesso (RBAC):
 * - Admin: Vê e gere todos os eventos.
 * - Operador: Vê e gere eventos da sua organização.
 * - Promoter: Vê eventos da sua organização (não pode criar/editar).
 * - Cliente: SÓ VÊ eventos publicados com data futura.
 * 
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Local;
use App\Models\Organizacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $query = Evento::with(['local', 'organizacao']);

        // ============================================================
        // REGRAS DE ACESSO (RBAC)
        // ============================================================
        if (auth()->user()->isCliente()) {
            $query->where('status', 'publicado')->where('data_fim', '>=', now()->toDateString());
        } elseif (auth()->user()->isOperador() || auth()->user()->isPromoter()) {
            $query->where('organizacao_id', auth()->user()->organizacao_id);
        }

        // Filtros
        if ($request->has('status') && $request->status) $query->where('status', $request->status);
        if ($request->has('search') && $request->search) $query->where('nome', 'like', "%{$request->search}%");
        if ($request->has('recorrente')) {
            if ($request->recorrente == '1') $query->where('recorrencia', '!=', 'nenhuma');
            elseif ($request->recorrente == '0') $query->where('recorrencia', 'nenhuma');
        }
        if ($request->has('organizacao_id') && $request->organizacao_id && auth()->user()->isAdmin()) {
            $query->where('organizacao_id', $request->organizacao_id);
        }

        $eventos = $query->orderBy('created_at', 'desc')->paginate(15);
        $organizacoes = auth()->user()->isAdmin() ? Organizacao::where('status', 'ativo')->orderBy('nome')->get() : [];

        return view('admin.eventos.index', compact('eventos', 'organizacoes'));
    }

    public function create()
    {
        // Segurança: Cliente e Promoter não criam eventos
        if (auth()->user()->isCliente() || auth()->user()->isPromoter()) {
            abort(403, 'Não têm permissão para criar eventos.');
        }

        $locais = Local::orderBy('nome')->get();
        $templates = Evento::where('is_template', true)->orderBy('nome')->get();
        $organizacoes = auth()->user()->isAdmin() ? Organizacao::where('status', 'ativo')->orderBy('nome')->get() : [];
        
        return view('admin.eventos.create', compact('locais', 'templates', 'organizacoes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'local_id' => 'required|exists:locais,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'hora_inicio' => 'required',
            'hora_fim' => 'required',
            'capacidade_total' => 'required|integer|min:1',
            'status' => ['required', Rule::in(['rascunho', 'publicado', 'encerrado', 'cancelado'])],
            'template_id' => 'nullable|exists:eventos,id',
            'recorrencia' => ['nullable', Rule::in(['nenhuma', 'diario', 'semanal', 'mensal', 'anual'])],
            'recorrencia_intervalo' => 'nullable|integer|min:1',
            'recorrencia_data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'is_template' => 'nullable|boolean',
            'organizacao_id' => 'nullable|exists:organizacoes,id',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();

        $data = $request->all();
        $data['uuid'] = (string) Str::uuid();
        $data['slug'] = Str::slug($request->nome . '-' . uniqid());
        $data['ingressos_vendidos'] = 0;
        $data['recorrencia'] = $data['recorrencia'] ?? 'nenhuma';
        $data['recorrencia_intervalo'] = $data['recorrencia_intervalo'] ?? 1;
        $data['is_template'] = $request->has('is_template') ? true : false;

        if (auth()->user()->organizacao_id) $data['organizacao_id'] = auth()->user()->organizacao_id;

        $evento = Evento::create($data);

        if ($request->has('template_id') && $request->template_id) {
            $template = Evento::find($request->template_id);
            if ($template) {
                foreach ($template->categoriasIngressos as $categoria) {
                    $novaCategoria = $categoria->replicate();
                    $novaCategoria->evento_id = $evento->id;
                    $novaCategoria->quantidade_vendida = 0;
                    $novaCategoria->save();
                }
            }
        }

        return redirect()->route('eventos.index')->with('success', '✅ Evento criado com sucesso!');
    }

    public function show($id)
    {
        $evento = Evento::with([
            'local', 'organizacao', 'categoriasIngressos',
            'ingressos' => function($query) { $query->selectRaw('status, COUNT(*) as total')->groupBy('status'); }
        ])->findOrFail($id);

        if (auth()->user()->isCliente()) {
            if ($evento->status !== 'publicado' || $evento->data_fim < now()->toDateString()) {
                abort(403, 'Este evento não está disponível para visualização.');
            }
        } elseif (!auth()->user()->podeGerenciarEvento($evento)) {
            abort(403, 'Você não tem permissão para visualizar este evento.');
        }

        $totalIngressos = $evento->ingressos->sum('total');
        $ingressosPagos = $evento->ingressos->where('status', 'pago')->sum('total');
        $ingressosUtilizados = $evento->ingressos->where('status', 'utilizado')->sum('total');
        $ingressosCancelados = $evento->ingressos->where('status', 'cancelado')->sum('total');

        return view('admin.eventos.show', compact('evento', 'totalIngressos', 'ingressosPagos', 'ingressosUtilizados', 'ingressosCancelados'));
    }

    public function edit($id)
    {
        $evento = Evento::findOrFail($id);
        if (!auth()->user()->podeGerenciarEvento($evento)) abort(403, 'Sem permissão.');
        
        $locais = Local::orderBy('nome')->get();
        $templates = Evento::where('is_template', true)->where('id', '!=', $id)->orderBy('nome')->get();
        $organizacoes = auth()->user()->isAdmin() ? Organizacao::where('status', 'ativo')->orderBy('nome')->get() : [];
        
        return view('admin.eventos.edit', compact('evento', 'locais', 'templates', 'organizacoes'));
    }

    public function update(Request $request, $id)
    {
        $evento = Evento::findOrFail($id);
        if (!auth()->user()->podeGerenciarEvento($evento)) abort(403, 'Sem permissão.');

        $validator = Validator::make($request->all(), [
            'local_id' => 'required|exists:locais,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'hora_inicio' => 'required',
            'hora_fim' => 'required',
            'capacidade_total' => 'required|integer|min:1',
            'status' => ['required', Rule::in(['rascunho', 'publicado', 'encerrado', 'cancelado'])],
            'recorrencia' => ['nullable', Rule::in(['nenhuma', 'diario', 'semanal', 'mensal', 'anual'])],
            'recorrencia_intervalo' => 'nullable|integer|min:1',
            'recorrencia_data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'is_template' => 'nullable|boolean',
            'organizacao_id' => 'nullable|exists:organizacoes,id',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();

        $data = $request->all();
        $data['recorrencia'] = $data['recorrencia'] ?? 'nenhuma';
        $data['recorrencia_intervalo'] = $data['recorrencia_intervalo'] ?? 1;
        $data['is_template'] = $request->has('is_template') ? true : false;

        $evento->update($data);
        return redirect()->route('eventos.index')->with('success', '✅ Evento atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);
        if (!auth()->user()->podeGerenciarEvento($evento)) abort(403, 'Sem permissão.');
        if ($evento->ingressos_vendidos > 0) return redirect()->back()->with('error', '❌ Não é possível excluir um evento com ingressos vendidos.');

        $evento->delete();
        return redirect()->route('eventos.index')->with('success', '✅ Evento removido com sucesso!');
    }

    // ============================================================
    // MÉTODOS ADICIONAIS E MULTI-EVENTOS
    // ============================================================

    public function publicar($id)
    {
        $evento = Evento::findOrFail($id);
        if (!auth()->user()->podeGerenciarEvento($evento)) abort(403, 'Sem permissão.');
        if ($evento->status === 'publicado') return redirect()->back()->with('error', '❌ Evento já está publicado.');
        
        $evento->status = 'publicado';
        $evento->save();
        return redirect()->route('eventos.index')->with('success', '✅ Evento publicado com sucesso!');
    }

    public function estatisticas($id)
    {
        $evento = Evento::with(['categoriasIngressos', 'ingressos', 'checkins'])->findOrFail($id);
        if (!auth()->user()->podeGerenciarEvento($evento)) abort(403, 'Sem permissão.');

        $totalVendas = $evento->ingressos->count();
        $totalReceita = $evento->ingressos->sum('preco_pago');
        $totalCheckins = $evento->checkins->count();
        
        $vendasPorCategoria = $evento->categoriasIngressos->map(function($categoria) {
            return ['nome' => $categoria->nome, 'total' => $categoria->quantidade_vendida, 'receita' => $categoria->quantidade_vendida * $categoria->preco];
        });

        return view('admin.eventos.estatisticas', compact('evento', 'totalVendas', 'totalReceita', 'totalCheckins', 'vendasPorCategoria'));
    }

    public function ativos()
    {
        $query = Evento::where('status', 'publicado')->where('data_fim', '>=', now());
        if (!auth()->user()->isAdmin()) $query->where('organizacao_id', auth()->user()->organizacao_id);
        $eventos = $query->orderBy('data_inicio', 'asc')->paginate(15);
        return view('admin.eventos.ativos', compact('eventos'));
    }

    public function duplicar($id)
    {
        try {
            $evento = Evento::with('categoriasIngressos')->findOrFail($id);
            if (!auth()->user()->podeGerenciarEvento($evento)) return response()->json(['success' => false, 'message' => 'Sem permissão.'], 403);
            $novoEvento = $evento->duplicar();
            return response()->json(['success' => true, 'message' => '✅ Evento duplicado!', 'evento_id' => $novoEvento->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function salvarTemplate($id)
    {
        try {
            $evento = Evento::findOrFail($id);
            if (!auth()->user()->podeGerenciarEvento($evento)) return response()->json(['success' => false, 'message' => 'Sem permissão.'], 403);
            if ($evento->is_template) return response()->json(['success' => false, 'message' => 'Já é um template.'], 400);

            $evento->is_template = true;
            $evento->save();
            return response()->json(['success' => true, 'message' => '✅ Template salvo!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function templates(Request $request)
    {
        $query = Evento::where('is_template', true)->with(['local', 'categoriasIngressos', 'organizacao']);
        if (!auth()->user()->isAdmin()) $query->where('organizacao_id', auth()->user()->organizacao_id);
        if ($request->has('search') && $request->search) $query->where('nome', 'like', "%{$request->search}%");
        $templates = $query->orderBy('nome')->paginate(12);
        return view('admin.eventos.templates', compact('templates'));
    }

    public function criarDoTemplate($id)
    {
        try {
            $template = Evento::with('categoriasIngressos')->where('is_template', true)->findOrFail($id);
            if (!auth()->user()->podeGerenciarEvento($template)) return redirect()->route('eventos.index')->with('error', '❌ Sem permissão.');
            $novoEvento = $template->duplicar(['nome' => $template->nome . ' (Novo)']);
            $novoEvento->template_id = $template->id;
            $novoEvento->save();
            return redirect()->route('eventos.edit', $novoEvento->id)->with('success', '✅ Evento criado a partir do template!');
        } catch (\Exception $e) {
            return redirect()->route('eventos.index')->with('error', '❌ Erro: ' . $e->getMessage());
        }
    }

    public function gerarRecorrentes($id)
    {
        try {
            $evento = Evento::findOrFail($id);
            if (!auth()->user()->podeGerenciarEvento($evento)) return redirect()->back()->with('error', '❌ Sem permissão.');
            if (!$evento->isRecorrente()) return redirect()->back()->with('error', '❌ Evento não é recorrente.');
            if (!$evento->recorrencia_data_fim) return redirect()->back()->with('error', '❌ Defina data de fim.');

            $eventosGerados = $evento->gerarEventosRecorrentes();
            return redirect()->route('eventos.index')->with('success', "✅ " . count($eventosGerados) . " eventos gerados!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ Erro: ' . $e->getMessage());
        }
    }

    public function desativarTemplate($id)
    {
        try {
            $evento = Evento::findOrFail($id);
            if (!auth()->user()->podeGerenciarEvento($evento)) return response()->json(['success' => false, 'message' => 'Sem permissão.'], 403);
            if (!$evento->is_template) return response()->json(['success' => false, 'message' => 'Não é um template.'], 400);

            $evento->is_template = false;
            $evento->save();
            return response()->json(['success' => true, 'message' => '✅ Template desativado!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function cancelar($id)
    {
        $evento = Evento::findOrFail($id);
        if (!auth()->user()->podeGerenciarEvento($evento)) abort(403, 'Sem permissão.');
        if ($evento->status === 'cancelado') return redirect()->back()->with('error', '❌ Já está cancelado.');

        $evento->status = 'cancelado';
        $evento->save();
        return redirect()->route('eventos.index')->with('success', '✅ Evento cancelado!');
    }

    public function restaurar($id)
    {
        $evento = Evento::findOrFail($id);
        if (!auth()->user()->podeGerenciarEvento($evento)) abort(403, 'Sem permissão.');
        if ($evento->status !== 'cancelado') return redirect()->back()->with('error', '❌ Apenas cancelados podem ser restaurados.');

        $evento->status = 'rascunho';
        $evento->save();
        return redirect()->route('eventos.index')->with('success', '✅ Evento restaurado!');
    }
}