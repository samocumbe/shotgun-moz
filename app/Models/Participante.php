<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Participante extends Model
{
    use HasFactory;

    protected $table = 'participantes';

    protected $fillable = [
        'uuid',
        'nome_completo',
        'email',
        'telefone',
        'nuit',
        'endereco',
        'data_nascimento',
        'user_id',  // NOVO CAMPO
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($participante) {
            if (empty($participante->uuid)) {
                $participante->uuid = (string) Str::uuid();
            }
        });
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    public function ingressos()
    {
        return $this->hasMany(Ingresso::class);
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * ============================================================
     * RELACIONAMENTO: Participante pertence a um User
     * ============================================================
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================================================
    // MÉTODOS DE NEGÓCIO
    // ============================================================

    public function temIngressoParaEvento(int $eventoId): bool
    {
        return $this->ingressos()
                    ->where('evento_id', $eventoId)
                    ->whereIn('status', ['pago', 'utilizado'])
                    ->exists();
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopePorEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopePorNuit($query, $nuit)
    {
        return $query->where('nuit', $nuit);
    }
}