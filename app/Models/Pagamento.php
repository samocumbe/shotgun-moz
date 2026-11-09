<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pagamento extends Model
{
    use HasFactory;

    protected $table = 'pagamentos';

    protected $fillable = [
        'uuid',
        'pedido_id',
        'valor',
        'metodo',
        'status',
        'referencia',
        'codigo_confirmacao',
        'dados_pagamento',
        'data_confirmacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'dados_pagamento' => 'array',
        'data_confirmacao' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pagamento) {
            if (empty($pagamento->uuid)) {
                $pagamento->uuid = (string) Str::uuid();
            }
        });
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function isConfirmado()
    {
        return $this->status === 'confirmado';
    }
}