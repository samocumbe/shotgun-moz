<?php

/**
 * ============================================================
 * MODEL: Evento
 * ============================================================
 * 
 * Representa eventos criados pelos organizadores.
 * 
 * Campos:
 * - id: Chave primária
 * - uuid: Identificador único universal
 * - local_id: ID do local onde o evento ocorrerá
 * - organizacao_id: ID da organização dona do evento (Multi-Organizador)
 * - evento_pai_id: ID do evento original (para duplicação)
 * - template_id: ID do template usado para criar este evento
 * - nome: Nome do evento
 * - slug: URL amigável
 * - descricao: Descrição detalhada
 * - data_inicio: Data de início
 * - data_fim: Data de término
 * - hora_inicio: Horário de início
 * - hora_fim: Horário de término
 * - capacidade_total: Capacidade máxima de público
 * - ingressos_vendidos: Total de ingressos vendidos
 * - status: rascunho, publicado, encerrado, cancelado
 * - recorrencia: nenhuma, diario, semanal, mensal, anual
 * - recorrencia_intervalo: Intervalo entre eventos
 * - recorrencia_data_fim: Data final para eventos recorrentes
 * - is_template: Indica se o evento é um template
 * - created_at: Data de criação
 * - updated_at: Data de atualização
 * 
 * Relacionamentos:
 * - belongsTo: Local
 * - belongsTo: Organizacao (Multi-Organizador)
 * - belongsTo: Evento (pai)
 * - belongsTo: Evento (template)
 * - hasMany: CategoriasIngressos
 * - hasMany: Ingressos
 * - hasMany: Checkins
 * - hasMany: Dispositivos
 * - hasMany: Eventos (filhos)
 * - hasMany: Eventos (do template)
 * 
 * Métodos:
 * - isPublicado(), isEncerrado(), isTemplate(), isRecorrente()
 * - temIngressosDisponiveis(), getCapacidadeDisponivel()
 * - incrementarIngressosVendidos()
 * - duplicar(), gerarEventosRecorrentes()
 * 
 * Scopes:
 * - publicados(), ativos(), templates(), recorrentes()
 * - porOrganizacao() (Multi-Organizador)
 * 
 * ============================================================
 */

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Evento extends Model
{

use HasFactory, Auditable;

    // ============================================================
    // CONSTANTES DE STATUS
    // ============================================================

    /**
     * Status possíveis para um evento
     */
    const STATUS_RASCUNHO = 'rascunho';
    const STATUS_PUBLICADO = 'publicado';
    const STATUS_ENCERRADO = 'encerrado';
    const STATUS_CANCELADO = 'cancelado';

    /**
     * Tipos de recorrência disponíveis
     */
    const RECORRENCIA_NENHUMA = 'nenhuma';
    const RECORRENCIA_DIARIO = 'diario';
    const RECORRENCIA_SEMANAL = 'semanal';
    const RECORRENCIA_MENSAL = 'mensal';
    const RECORRENCIA_ANUAL = 'anual';

    // ============================================================
    // CONFIGURAÇÃO DO MODEL
    // ============================================================

    /**
     * Atributos que podem ser preenchidos em massa (Mass Assignment)
     */
    protected $fillable = [
        'uuid',
        'local_id',
        'organizacao_id',   // 🔥 NOVO: Multi-Organizador
        'evento_pai_id',
        'template_id',
        'nome',
        'slug',
        'descricao',
        'data_inicio',
        'data_fim',
        'hora_inicio',
        'hora_fim',
        'capacidade_total',
        'ingressos_vendidos',
        'status',
        'recorrencia',
        'recorrencia_intervalo',
        'recorrencia_data_fim',
        'is_template',
    ];

    /**
     * Atributos que devem ser tratados como tipos específicos
     */
    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'recorrencia_data_fim' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fim' => 'datetime:H:i',
        'capacidade_total' => 'integer',
        'ingressos_vendidos' => 'integer',
        'recorrencia_intervalo' => 'integer',
        'is_template' => 'boolean',
    ];

    // ============================================================
    // BOOT DO MODELO
    // ============================================================

    /**
     * Boot do modelo: gera UUID e Slug automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($evento) {
            // Gerar UUID único se não existir
            if (empty($evento->uuid)) {
                $evento->uuid = (string) Str::uuid();
            }
            
            // Gerar Slug se não existir
            if (empty($evento->slug)) {
                $evento->slug = Str::slug($evento->nome . '-' . uniqid());
            }
            
            // Definir recorrência padrão se não definida
            if (empty($evento->recorrencia)) {
                $evento->recorrencia = self::RECORRENCIA_NENHUMA;
            }
            
            // Definir intervalo de recorrência padrão
            if (empty($evento->recorrencia_intervalo)) {
                $evento->recorrencia_intervalo = 1;
            }
            
            // Definir is_template como false se não definido
            if (!isset($evento->is_template)) {
                $evento->is_template = false;
            }

            // 🔥 NOVO: Se o usuário logado tem organização, atribuir automaticamente
            if (auth()->check() && auth()->user()->organizacao_id) {
                $evento->organizacao_id = auth()->user()->organizacao_id;
            }
        });

        // Evento após criar: se for template, log
        static::created(function ($evento) {
            if ($evento->is_template) {
                Log::info('Template criado: ' . $evento->id . ' - ' . $evento->nome);
            }
        });
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    /**
     * Relacionamento: Evento pertence a um Local
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    /**
     * 🔥 NOVO: Relacionamento: Evento pertence a uma Organização (Multi-Organizador)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }

    /**
     * Relacionamento: Evento tem muitas Categorias de Ingresso
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categoriasIngressos()
    {
        return $this->hasMany(CategoriaIngresso::class);
    }

    /**
     * Relacionamento: Evento tem muitos Ingressos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ingressos()
    {
        return $this->hasMany(Ingresso::class);
    }

    /**
     * Relacionamento: Evento tem muitos Checkins
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Relacionamento: Evento tem muitos Dispositivos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dispositivos()
    {
        return $this->hasMany(Dispositivo::class);
    }

    // ============================================================
    // MULTI-EVENTOS: RELACIONAMENTOS
    // ============================================================

    /**
     * Relacionamento: Evento pai (para duplicação)
     * 
     * Indica de qual evento este foi duplicado
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventoPai()
    {
        return $this->belongsTo(Evento::class, 'evento_pai_id');
    }

    /**
     * Relacionamento: Eventos filhos (duplicações deste evento)
     * 
     * Retorna todos os eventos que foram duplicados deste
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventosFilhos()
    {
        return $this->hasMany(Evento::class, 'evento_pai_id');
    }

    /**
     * Relacionamento: Template usado para criar este evento
     * 
     * Indica qual template foi usado como base
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(Evento::class, 'template_id');
    }

    /**
     * Relacionamento: Eventos criados a partir deste template
     * 
     * Retorna todos os eventos que usaram este como template
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventosDoTemplate()
    {
        return $this->hasMany(Evento::class, 'template_id');
    }

    // ============================================================
    // MÉTODOS DE VERIFICAÇÃO DE STATUS
    // ============================================================

    /**
     * Verifica se o evento está publicado e disponível
     * 
     * @return bool
     */
    public function isPublicado(): bool
    {
        return $this->status === self::STATUS_PUBLICADO;
    }

    /**
     * Verifica se o evento está encerrado
     * 
     * @return bool
     */
    public function isEncerrado(): bool
    {
        return $this->status === self::STATUS_ENCERRADO;
    }

    /**
     * Verifica se o evento está cancelado
     * 
     * @return bool
     */
    public function isCancelado(): bool
    {
        return $this->status === self::STATUS_CANCELADO;
    }

    /**
     * Verifica se o evento é um template
     * 
     * @return bool
     */
    public function isTemplate(): bool
    {
        return $this->is_template === true;
    }

    /**
     * Verifica se o evento é recorrente
     * 
     * @return bool
     */
    public function isRecorrente(): bool
    {
        return $this->recorrencia !== self::RECORRENCIA_NENHUMA;
    }

    /**
     * Verifica se o evento está ativo (publicado e não encerrado)
     * 
     * @return bool
     */
    public function isAtivo(): bool
    {
        return $this->isPublicado() && $this->data_fim >= now();
    }

    // ============================================================
    // MÉTODOS DE CAPACIDADE
    // ============================================================

    /**
     * Verifica se ainda há ingressos disponíveis
     * 
     * @return bool
     */
    public function temIngressosDisponiveis(): bool
    {
        return $this->ingressos_vendidos < $this->capacidade_total;
    }

    /**
     * Retorna a capacidade disponível
     * 
     * @return int
     */
    public function getCapacidadeDisponivel(): int
    {
        return $this->capacidade_total - $this->ingressos_vendidos;
    }

    /**
     * Incrementa o contador de ingressos vendidos
     * 
     * @param int $quantidade
     * @return void
     */
    public function incrementarIngressosVendidos(int $quantidade = 1): void
    {
        $this->increment('ingressos_vendidos', $quantidade);
    }

    /**
     * Decrementa o contador de ingressos vendidos
     * 
     * @param int $quantidade
     * @return void
     */
    public function decrementarIngressosVendidos(int $quantidade = 1): void
    {
        $this->decrement('ingressos_vendidos', $quantidade);
    }

    // ============================================================
    // MÉTODOS DE MULTI-EVENTOS
    // ============================================================

    /**
     * Duplicar o evento com suas categorias
     * 
     * @param array $dados Dados adicionais para o novo evento
     * @return self
     * 
     * Fluxo:
     * 1. Replica o evento (copia todos os campos)
     * 2. Gera novo UUID (não copiar o existente)
     * 3. Gera novo Slug
     * 4. Marca como rascunho
     * 5. Zera ingressos vendidos
     * 6. Remove template_id e recorrência
     * 7. Mantém a organização (Multi-Organizador)
     * 8. Salva o novo evento
     * 9. Duplica todas as categorias de ingressos
     * 10. Retorna o novo evento
     */
    public function duplicar(array $dados = []): self
    {
        // Replicar o evento (cria uma cópia em memória)
        $novoEvento = $this->replicate();
        
        // Gerar novo UUID (não copiar o existente)
        $novoEvento->uuid = (string) Str::uuid();
        
        // Definir dados do novo evento
        $novoEvento->nome = $dados['nome'] ?? $this->nome . ' (Cópia)';
        $novoEvento->slug = Str::slug($novoEvento->nome . '-' . uniqid());
        $novoEvento->evento_pai_id = $this->id;
        $novoEvento->status = 'rascunho';
        $novoEvento->ingressos_vendidos = 0;
        $novoEvento->is_template = false;
        $novoEvento->recorrencia = self::RECORRENCIA_NENHUMA;
        $novoEvento->recorrencia_intervalo = 1;
        $novoEvento->recorrencia_data_fim = null;
        $novoEvento->template_id = null;
        
        // 🔥 NOVO: Manter a organização do evento original
        $novoEvento->organizacao_id = $this->organizacao_id;
        
        // Salvar o novo evento
        $novoEvento->save();

        // Duplicar categorias de ingressos
        foreach ($this->categoriasIngressos as $categoria) {
            $novaCategoria = $categoria->replicate();
            $novaCategoria->evento_id = $novoEvento->id;
            $novaCategoria->quantidade_vendida = 0;
            $novaCategoria->save();
        }

        Log::info('Evento duplicado: ' . $this->id . ' (' . $this->nome . ') -> ' . $novoEvento->id . ' (' . $novoEvento->nome . ')');

        return $novoEvento;
    }

    /**
     * Gerar eventos recorrentes a partir deste evento
     * 
     * @return array Lista de eventos gerados
     * 
     * Suporta:
     * - Diário (a cada X dias)
     * - Semanal (a cada X semanas)
     * - Mensal (a cada X meses)
     * - Anual (a cada X anos)
     * 
     * Gera eventos para cada data no intervalo definido,
     * excluindo a data do evento original.
     */
    public function gerarEventosRecorrentes(): array
    {
        // Verificar se o evento é recorrente
        if (!$this->isRecorrente() || !$this->recorrencia_data_fim) {
            Log::warning('Evento não é recorrente ou não tem data de fim: ' . $this->id);
            return [];
        }

        $eventosGerados = [];
        $dataAtual = clone $this->data_inicio;
        $dataFim = clone $this->recorrencia_data_fim;
        $contador = 0;

        // Limite de segurança: máximo 100 eventos recorrentes
        $limiteMaximo = 100;

        while ($dataAtual <= $dataFim && $contador < $limiteMaximo) {
            // Pular a data do evento original
            if ($dataAtual->format('Y-m-d') !== $this->data_inicio->format('Y-m-d')) {
                // Usar o método duplicar() corrigido
                $novoEvento = $this->duplicar([
                    'nome' => $this->nome . ' (' . $dataAtual->format('d/m/Y') . ')'
                ]);
                
                // Atualizar as datas do novo evento
                $novoEvento->data_inicio = $dataAtual->format('Y-m-d');
                $novoEvento->data_fim = $dataAtual->format('Y-m-d');
                $novoEvento->status = $this->status;
                $novoEvento->save();

                $eventosGerados[] = $novoEvento;
            }

            // Avançar para a próxima data conforme a recorrência
            switch ($this->recorrencia) {
                case self::RECORRENCIA_DIARIO:
                    $dataAtual->addDays($this->recorrencia_intervalo);
                    break;
                case self::RECORRENCIA_SEMANAL:
                    $dataAtual->addWeeks($this->recorrencia_intervalo);
                    break;
                case self::RECORRENCIA_MENSAL:
                    $dataAtual->addMonths($this->recorrencia_intervalo);
                    break;
                case self::RECORRENCIA_ANUAL:
                    $dataAtual->addYears($this->recorrencia_intervalo);
                    break;
                default:
                    break;
            }
            
            $contador++;
        }

        if ($contador >= $limiteMaximo) {
            Log::warning('Limite de eventos recorrentes atingido: ' . $limiteMaximo . ' para o evento ' . $this->id);
        }

        Log::info('Eventos recorrentes gerados: ' . count($eventosGerados) . ' a partir do evento ' . $this->id);

        return $eventosGerados;
    }

    /**
     * Salvar como template
     * 
     * @return void
     */
    public function salvarComoTemplate(): void
    {
        $this->is_template = true;
        $this->save();
        
        Log::info('Evento salvo como template: ' . $this->id . ' - ' . $this->nome);
    }

    /**
     * Remover template
     * 
     * @return void
     */
    public function removerTemplate(): void
    {
        $this->is_template = false;
        $this->save();
        
        Log::info('Template removido: ' . $this->id . ' - ' . $this->nome);
    }

    // ============================================================
    // MÉTODOS DE FORMATAÇÃO
    // ============================================================

    /**
     * Retorna a data de início formatada
     * 
     * @return string
     */
    public function getDataInicioFormatada(): string
    {
        return $this->data_inicio->format('d/m/Y');
    }

    /**
     * Retorna a data de fim formatada
     * 
     * @return string
     */
    public function getDataFimFormatada(): string
    {
        return $this->data_fim->format('d/m/Y');
    }

    /**
     * Retorna o horário de início formatado
     * 
     * @return string
     */
    public function getHoraInicioFormatada(): string
    {
        return $this->hora_inicio->format('H:i');
    }

    /**
     * Retorna o horário de fim formatado
     * 
     * @return string
     */
    public function getHoraFimFormatada(): string
    {
        return $this->hora_fim->format('H:i');
    }

    /**
     * Retorna o status em português
     * 
     * @return string
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_RASCUNHO => 'Rascunho',
            self::STATUS_PUBLICADO => 'Publicado',
            self::STATUS_ENCERRADO => 'Encerrado',
            self::STATUS_CANCELADO => 'Cancelado',
            default => $this->status
        };
    }

    /**
     * Retorna a cor do status para badges
     * 
     * @return string
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_RASCUNHO => 'secondary',
            self::STATUS_PUBLICADO => 'success',
            self::STATUS_ENCERRADO => 'info',
            self::STATUS_CANCELADO => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Retorna o tipo de recorrência em português
     * 
     * @return string
     */
    public function getRecorrenciaLabel(): string
    {
        return match($this->recorrencia) {
            self::RECORRENCIA_NENHUMA => 'Nenhuma',
            self::RECORRENCIA_DIARIO => 'Diário',
            self::RECORRENCIA_SEMANAL => 'Semanal',
            self::RECORRENCIA_MENSAL => 'Mensal',
            self::RECORRENCIA_ANUAL => 'Anual',
            default => $this->recorrencia
        };
    }

    // ============================================================
    // SCOPES (Filtros)
    // ============================================================

    /**
     * Escopo: Filtrar apenas eventos publicados
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublicados($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO);
    }

    /**
     * Escopo: Filtrar eventos ativos (publicados e não encerrados)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO)
                     ->where('data_fim', '>=', now());
    }

    /**
     * Escopo: Filtrar apenas templates
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    /**
     * Escopo: Filtrar eventos recorrentes
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecorrentes($query)
    {
        return $query->where('recorrencia', '!=', self::RECORRENCIA_NENHUMA);
    }

    /**
     * Escopo: Filtrar por status
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Escopo: Filtrar por local
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $localId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorLocal($query, $localId)
    {
        return $query->where('local_id', $localId);
    }

    /**
     * 🔥 NOVO: Escopo: Filtrar por organização (Multi-Organizador)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $organizacaoId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorOrganizacao($query, $organizacaoId)
    {
        return $query->where('organizacao_id', $organizacaoId);
    }

    /**
     * Escopo: Filtrar eventos com ingressos disponíveis
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComIngressosDisponiveis($query)
    {
        return $query->whereColumn('ingressos_vendidos', '<', 'capacidade_total');
    }
}