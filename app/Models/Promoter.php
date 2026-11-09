<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Promoter extends Model
{
    use HasFactory;

    protected $table = 'promoters';

    protected $fillable = [
        'uuid',
        'nome',
        'email',
        'telefone',
        'nuit',
        'codigo_promocional',
        'link_personalizado',
        'qr_code',
        'comissao_padrao',
        'total_vendas',
        'total_comissao',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'comissao_padrao' => 'decimal:2',
        'total_vendas' => 'decimal:2',
        'total_comissao' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($promoter) {
            if (empty($promoter->uuid)) {
                $promoter->uuid = (string) Str::uuid();
            }
            if (empty($promoter->codigo_promocional)) {
                $promoter->codigo_promocional = 'PROMO-' . strtoupper(Str::random(8));
            }
            if (empty($promoter->link_personalizado)) {
                $promoter->link_personalizado = 'promoter/' . Str::slug($promoter->nome) . '-' . Str::random(6);
            }
        });
    }

    // Relacionamentos
    public function eventos()
    {
        return $this->belongsToMany(Evento::class, 'promoter_eventos')
                    ->withPivot('comissao_personalizada', 'ingressos_alocados', 'ingressos_vendidos', 'status')
                    ->withTimestamps();
    }

    public function comissoes()
    {
        return $this->hasMany(Comissao::class);
    }

    public function vendasPromoter()
    {
        return $this->hasMany(VendaPromoter::class);
    }

    // Métodos
    public function getComissaoParaEvento($eventoId)
    {
        $evento = $this->eventos()->where('evento_id', $eventoId)->first();
        return $evento ? ($evento->pivot->comissao_personalizada ?? $this->comissao_padrao) : $this->comissao_padrao;
    }

    public function isAtivo()
    {
        return $this->status === 'ativo';
    }

    public function gerarQRCode()
    {
        // Implementar geração de QR Code
        return $this->link_personalizado;
    }
}