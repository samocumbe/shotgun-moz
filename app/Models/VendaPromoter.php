<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VendaPromoter extends Model
{
    use HasFactory;

    protected $table = 'vendas_promoter';

    protected $fillable = [
        'uuid',
        'promoter_id',
        'venda_id',
        'pedido_id',
        'codigo_promocional_usado',
        'valor_total',
        'comissao_gerada',
        'status',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'comissao_gerada' => 'decimal:2',
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
}