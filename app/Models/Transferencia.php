<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transferencia extends Model
{
    use HasFactory;

    protected $table = 'transferencias';

    protected $fillable = [
        'uuid',
        'carteira_ingresso_id',
        'usuario_origem_id',
        'usuario_destino_id',
        'codigo_confirmacao',
        'status',
        'data_confirmacao',
    ];

    protected $casts = [
        'data_confirmacao' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transferencia) {
            if (empty($transferencia->uuid)) {
                $transferencia->uuid = (string) Str::uuid();
            }
            if (empty($transferencia->codigo_confirmacao)) {
                $transferencia->codigo_confirmacao = 'TRF-' . strtoupper(Str::random(8));
            }
        });
    }

    public function carteiraIngresso()
    {
        return $this->belongsTo(CarteiraIngresso::class);
    }

    public function usuarioOrigem()
    {
        return $this->belongsTo(User::class, 'usuario_origem_id');
    }

    public function usuarioDestino()
    {
        return $this->belongsTo(User::class, 'usuario_destino_id');
    }
}