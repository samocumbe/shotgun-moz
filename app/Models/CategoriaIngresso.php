<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model CategoriaIngresso
 * 
 * Representa as diferentes categorias/tipos de ingressos para um evento
 * Ex: Normal, VIP, Backstage, Meia-entrada
 * 
 * @property int $id
 * @property int $evento_id
 * @property string $nome
 * @property string $slug
 * @property string|null $descricao
 * @property float $preco
 * @property int $quantidade_total
 * @property int $quantidade_vendida
 * @property string $tipo
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read Evento $evento
 * @property-read \Illuminate\Database\Eloquent\Collection|Ingresso[] $ingressos
 */
class CategoriaIngresso extends Model
{
    use HasFactory;

protected $table = 'categorias_ingressos';

    /**
     * Tipos de ingresso disponíveis
     */
    const TIPO_NORMAL = 'normal';
    const TIPO_VIP = 'vip';
    const TIPO_BACKSTAGE = 'backstage';
    const TIPO_MEIA = 'meia';
    const TIPO_ESTUDANTE = 'estudante';

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'evento_id',
        'nome',
        'slug',
        'descricao',
        'preco',
        'quantidade_total',
        'quantidade_vendida',
        'tipo',
    ];

    /**
     * Atributos que devem ser tratados como tipos específicos
     */
    protected $casts = [
        'preco' => 'decimal:2',
        'quantidade_total' => 'integer',
        'quantidade_vendida' => 'integer',
    ];

    /**
     * Boot do modelo: gera Slug automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nome . '-' . uniqid());
            }
        });
    }

    /**
     * Relacionamento: Categoria pertence a um Evento
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Relacionamento: Categoria tem muitos Ingressos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ingressos()
    {
        return $this->hasMany(Ingresso::class);
    }

    /**
     * Verifica se ainda há ingressos disponíveis nesta categoria
     * 
     * @return bool
     */
    public function temIngressosDisponiveis(): bool
    {
        return $this->quantidade_vendida < $this->quantidade_total;
    }

    /**
     * Verifica a quantidade disponível nesta categoria
     * 
     * @return int
     */
    public function getCapacidadeDisponivel(): int
    {
        return $this->quantidade_total - $this->quantidade_vendida;
    }

    /**
     * Incrementa o contador de ingressos vendidos da categoria
     * 
     * @param int $quantidade
     * @return void
     */
    public function incrementarVendidos(int $quantidade = 1): void
    {
        $this->increment('quantidade_vendida', $quantidade);
    }

    /**
     * Escopo: Filtrar categorias com ingressos disponíveis
     */
    public function scopeDisponiveis($query)
    {
        return $query->whereRaw('quantidade_vendida < quantidade_total');
    }
}