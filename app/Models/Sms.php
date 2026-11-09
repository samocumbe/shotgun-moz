<?php

/**
 * ============================================================
 * MODEL: Sms - SHOTGUN-MOZ
 * ============================================================
 *
 * Representa cada SMS enviado (ou tentado enviar) pelo sistema.
 *
 * Campos principais:
 * - id                : Chave primária
 * - uuid              : Identificador único
 * - user_id           : Utilizador que disparou o envio
 * - organizacao_id    : Organização do utilizador (Multi-Organizador)
 * - evento_id         : Evento associado (opcional)
 * - template_id       : Template usado (opcional) 🆕
 * - telefone          : Número de destino (formato +258...)
 * - mensagem          : Corpo final do SMS (já renderizado)
 * - status            : pendente | enviado | falha | entregue
 * - provedor          : log | twilio | africastalking
 * - referencia        : ID do provedor / lote de envio em massa
 * - data_envio        : Timestamp do envio
 * - data_entrega      : Timestamp da entrega (quando reportado)
 * - tentativas        : Contador de tentativas
 * - erro              : Última mensagem de erro (se houver)
 *
 * Relacionamentos:
 * - belongsTo: User
 * - belongsTo: Organizacao
 * - belongsTo: Evento
 * - belongsTo: SmsTemplate  🆕
 *
 * Scopes:
 * - pendentes() | enviados() | falhas()
 *
 * ============================================================
 */

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sms extends Model
{
    use HasFactory, Auditable;

    /**
     * Nome da tabela
     */
    protected $table = 'sms';

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'organizacao_id',
        'evento_id',
        'template_id',      // 🆕 template usado (opcional)
        'telefone',
        'mensagem',
        'status',
        'provedor',
        'referencia',
        'data_envio',
        'data_entrega',
        'tentativas',
        'erro',
    ];

    /**
     * Casts de tipos
     */
    protected $casts = [
        'data_envio'    => 'datetime',
        'data_entrega'  => 'datetime',
        'tentativas'    => 'integer',
    ];

    /**
     * Boot: gerar UUID automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sms) {
            if (empty($sms->uuid)) {
                $sms->uuid = (string) Str::uuid();
            }
            if (empty($sms->status)) {
                $sms->status = 'pendente';
            }
            if (!isset($sms->tentativas)) {
                $sms->tentativas = 0;
            }
        });
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    /**
     * Utilizador que disparou o envio
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Organização à qual pertence o SMS (Multi-Organizador)
     */
    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }

    /**
     * Evento associado (opcional)
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * 🆕 Template de SMS usado no envio (opcional)
     */
    public function template()
    {
        return $this->belongsTo(SmsTemplate::class, 'template_id');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeEnviados($query)
    {
        return $query->where('status', 'enviado');
    }

    public function scopeFalhas($query)
    {
        return $query->where('status', 'falha');
    }

    public function scopeEntregues($query)
    {
        return $query->where('status', 'entregue');
    }

    // ============================================================
    // MÉTODOS DE ESTADO
    // ============================================================

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isEnviado(): bool
    {
        return $this->status === 'enviado';
    }

    public function isFalha(): bool
    {
        return $this->status === 'falha';
    }

    public function isEntregue(): bool
    {
        return $this->status === 'entregue';
    }

    // ============================================================
    // AÇÕES DE ESTADO
    // ============================================================

    /**
     * Marca como enviado com sucesso
     */
    public function marcarComoEnviado(): void
    {
        $this->status     = 'enviado';
        $this->data_envio = now();
        $this->tentativas = ($this->tentativas ?? 0) + 1;
        $this->save();
    }

    /**
     * Marca como entregue (quando o provedor reporta)
     */
    public function marcarComoEntregue(): void
    {
        $this->status       = 'entregue';
        $this->data_entrega = now();
        $this->save();
    }

    /**
     * Marca como falha, guardando a mensagem de erro
     */
    public function marcarComoFalha(string $erro): void
    {
        $this->status     = 'falha';
        $this->erro       = $erro;
        $this->tentativas = ($this->tentativas ?? 0) + 1;
        $this->save();
    }
}