<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'uuid',
        'evento_id',
        'participante_id',
        'user_id',
        'numero_pedido',
        'subtotal',
        'desconto',
        'total',
        'status',
        'tipo_venda',
        'observacoes',
        'data_pagamento',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'desconto' => 'decimal:2',
        'total' => 'decimal:2',
        'data_pagamento' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pedido) {
            if (empty($pedido->uuid)) {
                $pedido->uuid = (string) Str::uuid();
            }
            if (empty($pedido->numero_pedido)) {
                $pedido->numero_pedido = 'PED-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function participante()
    {
        return $this->belongsTo(Participante::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
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