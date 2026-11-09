<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reembolso extends Model
{
    use HasFactory;

    protected $table = 'reembolsos';

    protected $fillable = [
        'uuid',
        'carteira_ingresso_id',
        'user_id',
        'valor_reembolso',
        'motivo',
        'observacoes',
        'status',
        'data_solicitacao',
        'data_aprovacao',
        'data_processamento',
    ];

    protected $casts = [
        'valor_reembolso' => 'decimal:2',
        'data_solicitacao' => 'datetime',
        'data_aprovacao' => 'datetime',
        'data_processamento' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reembolso) {
            if (empty($reembolso->uuid)) {
                $reembolso->uuid = (string) Str::uuid();
            }
            if (empty($reembolso->data_solicitacao)) {
                $reembolso->data_solicitacao = now();
            }
        });
    }

    public function carteiraIngresso()
    {
        return $this->belongsTo(CarteiraIngresso::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPendente()
    {
        return $this->status === 'pendente';
    }

    public function isAprovado()
    {
        return $this->status === 'aprovado';
    }

    public function isRejeitado()
    {
        return $this->status === 'rejeitado';
    }

    public function isProcessado()
    {
        return $this->status === 'processado';
    }
}