<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Comissao extends Model
{
    use HasFactory;

    protected $table = 'comissoes';

    protected $fillable = [
        'uuid',
        'promoter_id',
        'venda_id',
        'pedido_id',
        'valor_venda',
        'percentual_comissao',
        'valor_comissao',
        'status',
        'data_pagamento',
        'observacoes',
    ];

    protected $casts = [
        'valor_venda' => 'decimal:2',
        'percentual_comissao' => 'decimal:2',
        'valor_comissao' => 'decimal:2',
        'data_pagamento' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($comissao) {
            if (empty($comissao->uuid)) {
                $comissao->uuid = (string) Str::uuid();
            }
        });
    }

    public function promoter()
    {
        return $this->belongsTo(Promoter::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function isPago()
    {
        return $this->status === 'pago';
    }

    public function isPendente()
    {
        return $this->status === 'pendente';
    }
}