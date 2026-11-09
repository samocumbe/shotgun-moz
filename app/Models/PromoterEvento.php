<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoterEvento extends Model
{
    use HasFactory;

    protected $table = 'promoter_eventos';

    protected $fillable = [
        'promoter_id',
        'evento_id',
        'comissao_personalizada',
        'ingressos_alocados',
        'ingressos_vendidos',
        'status',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'comissao_personalizada' => 'decimal:2',
        'ingressos_alocados' => 'integer',
        'ingressos_vendidos' => 'integer',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public function promoter()
    {
        return $this->belongsTo(Promoter::class);
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function getComissaoEfetiva()
    {
        return $this->comissao_personalizada ?? $this->promoter->comissao_padrao;
    }

    public function temIngressosDisponiveis()
    {
        return $this->ingressos_alocados > $this->ingressos_vendidos;
    }
}