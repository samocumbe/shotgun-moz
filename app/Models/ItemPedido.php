<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    use HasFactory;

    protected $table = 'itens_pedido';

    protected $fillable = [
        'pedido_id',
        'categoria_ingresso_id',
        'ingresso_id',
        'quantidade',
        'preco_unitario',
        'subtotal',
    ];

    protected $casts = [
        'preco_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantidade' => 'integer',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaIngresso::class, 'categoria_ingresso_id');
    }

    public function ingresso()
    {
        return $this->belongsTo(Ingresso::class);
    }
}