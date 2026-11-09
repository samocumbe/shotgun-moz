<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CarteiraIngresso extends Model
{
    use HasFactory;

    protected $table = 'carteira_ingressos';

    // Status possíveis
    const STATUS_ATIVO = 'ativo';
    const STATUS_UTILIZADO = 'utilizado';
    const STATUS_TRANSFERIDO = 'transferido';
    const STATUS_REEMBOLSADO = 'reembolsado';

    protected $fillable = [
        'uuid',
        'user_id',
        'ingresso_id',
        'codigo_acesso',
        'status',
        'data_ativacao',
        'data_utilizacao',
        'data_transferencia',  // ← ADICIONADO
        'data_reembolso',      // ← ADICIONADO
        'observacoes',
    ];

    protected $casts = [
        'data_ativacao' => 'datetime',
        'data_utilizacao' => 'datetime',
        'data_transferencia' => 'datetime',  // ← ADICIONADO
        'data_reembolso' => 'datetime',      // ← ADICIONADO
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($carteira) {
            if (empty($carteira->uuid)) {
                $carteira->uuid = (string) Str::uuid();
            }
            if (empty($carteira->codigo_acesso)) {
                $carteira->codigo_acesso = 'ACC-' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(4));
            }
            if (empty($carteira->status)) {
                $carteira->status = self::STATUS_ATIVO;
            }
            if (empty($carteira->data_ativacao)) {
                $carteira->data_ativacao = now();
            }
        });
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ingresso()
    {
        return $this->belongsTo(Ingresso::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeAtivos($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ============================================================
    // MÉTODOS DE VERIFICAÇÃO
    // ============================================================

    public function isAtivo(): bool
    {
        return $this->status === self::STATUS_ATIVO;
    }

    public function isUtilizado(): bool
    {
        return $this->status === self::STATUS_UTILIZADO;
    }

    public function isTransferido(): bool
    {
        return $this->status === self::STATUS_TRANSFERIDO;
    }

    public function isReembolsado(): bool
    {
        return $this->status === self::STATUS_REEMBOLSADO;
    }

    // ============================================================
    // MÉTODOS DE AÇÃO
    // ============================================================

    public function marcarComoUtilizado(): void
    {
        $this->status = self::STATUS_UTILIZADO;
        $this->data_utilizacao = now();
        $this->save();
    }

    public function marcarComoTransferido(): void
    {
        $this->status = self::STATUS_TRANSFERIDO;
        $this->data_transferencia = now();  // ← AGORA FUNCIONA
        $this->save();
    }

    public function marcarComoReembolsado(): void
    {
        $this->status = self::STATUS_REEMBOLSADO;
        $this->data_reembolso = now();      // ← AGORA FUNCIONA
        $this->save();
    }

    public function reativar(): void
    {
        $this->status = self::STATUS_ATIVO;
        $this->data_ativacao = now();
        $this->data_transferencia = null;   // ← Limpar data de transferência
        $this->data_reembolso = null;       // ← Limpar data de reembolso
        $this->save();
    }
}