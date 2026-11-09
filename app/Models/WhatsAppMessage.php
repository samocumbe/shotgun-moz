<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppMessage extends Model
{
    use HasFactory, Auditable;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'uuid',
        'user_id',
        'organizacao_id',
        'evento_id',
        'telefone',
        'mensagem',
        'template_id',
        'status',
        'provedor',
        'referencia',
        'data_envio',
        'data_entrega',
        'data_leitura',
        'tentativas',
        'erro',
    ];

    protected $casts = [
        'data_envio' => 'datetime',
        'data_entrega' => 'datetime',
        'data_leitura' => 'datetime',
        'tentativas' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            if (empty($message->uuid)) {
                $message->uuid = (string) Str::uuid();
            }
            if (empty($message->status)) {
                $message->status = 'pendente';
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

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
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

    // ============================================================
    // MÉTODOS
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

    public function isLido(): bool
    {
        return $this->status === 'lido';
    }

    public function marcarComoEnviado(): void
    {
        $this->status = 'enviado';
        $this->data_envio = now();
        $this->save();
    }

    public function marcarComoEntregue(): void
    {
        $this->status = 'entregue';
        $this->data_entrega = now();
        $this->save();
    }

    public function marcarComoLido(): void
    {
        $this->status = 'lido';
        $this->data_leitura = now();
        $this->save();
    }

    public function marcarComoFalha(string $erro): void
    {
        $this->status = 'falha';
        $this->erro = $erro;
        $this->save();
    }
}