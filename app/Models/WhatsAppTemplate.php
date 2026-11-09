<?php

/**
 * ============================================================
 * MODEL: WhatsAppTemplate - SHOTGUN-MOZ
 * ============================================================
 *
 * Representa um template de mensagem WhatsApp.
 *
 * Campos:
 * - id: Chave primária
 * - uuid: Identificador único
 * - organizacao_id: Organização proprietária
 * - nome: Nome amigável do template
 * - codigo: Código único (identificador no WhatsApp Business)
 * - mensagem: Corpo da mensagem (pode conter {{variavel}})
 * - variaveis: Lista de variáveis esperadas (JSON)
 * - categoria: marketing | utilidade | autenticacao
 * - status: pendente | aprovado | rejeitado
 * - is_active: Se o template está ativo
 *
 * Relacionamentos:
 * - belongsTo: Organizacao
 *
 * ============================================================
 */

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppTemplate extends Model
{
    use HasFactory, Auditable;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'uuid',
        'organizacao_id',
        'nome',
        'codigo',
        'mensagem',
        'variaveis',
        'categoria',
        'status',
        'is_active',
    ];

    protected $casts = [
        'variaveis' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->uuid)) {
                $template->uuid = (string) Str::uuid();
            }
            if (empty($template->status)) {
                $template->status = 'pendente';
            }
            if (!isset($template->is_active)) {
                $template->is_active = true;
            }
            if (empty($template->codigo)) {
                $template->codigo = strtoupper(Str::slug($template->nome, '_'));
            }
        });
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeAtivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAprovados($query)
    {
        return $query->where('status', 'aprovado');
    }

    // ============================================================
    // MÉTODOS
    // ============================================================

    public function isAtivo(): bool
    {
        return $this->is_active === true;
    }

    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isRejeitado(): bool
    {
        return $this->status === 'rejeitado';
    }

    /**
     * Substitui as variáveis {{chave}} na mensagem pelos valores fornecidos
     */
    public function render(array $valores = []): string
    {
        $mensagem = $this->mensagem;

        foreach ($valores as $chave => $valor) {
            $mensagem = str_replace('{{' . $chave . '}}', $valor, $mensagem);
            $mensagem = str_replace('{' . $chave . '}', $valor, $mensagem);
        }

        return $mensagem;
    }
}