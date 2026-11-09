<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';

    protected $fillable = [
        'uuid',
        'evento_id',
        'user_id',
        'pedido_id',
        'participante_id',
        'tipo',
        'valor_total',
        'status',
        'data_venda',
        'observacoes',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'data_venda' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($venda) {
            if (empty($venda->uuid)) {
                $venda->uuid = (string) Str::uuid();
            }
        });
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function participante()
    {
        return $this->belongsTo(Participante::class);
    }
}