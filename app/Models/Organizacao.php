<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Organizacao extends Model
{
    use HasFactory;

    /**
     * 🔥 CORREÇÃO: Definir o nome correto da tabela
     */
    protected $table = 'organizacoes';

    protected $fillable = [
        'uuid',
        'nome',
        'slug',
        'descricao',
        'email',
        'telefone',
        'endereco',
        'logo',
        'website',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organizacao) {
            if (empty($organizacao->uuid)) {
                $organizacao->uuid = (string) Str::uuid();
            }
            if (empty($organizacao->slug)) {
                $organizacao->slug = Str::slug($organizacao->nome . '-' . uniqid());
            }
            if (empty($organizacao->status)) {
                $organizacao->status = 'ativo';
            }
        });
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ============================================================
    // MÉTODOS DE VERIFICAÇÃO
    // ============================================================

    public function isAtivo(): bool
    {
        return $this->status === 'ativo';
    }

    public function isInativo(): bool
    {
        return $this->status === 'inativo';
    }

    public function isSuspenso(): bool
    {
        return $this->status === 'suspenso';
    }
}