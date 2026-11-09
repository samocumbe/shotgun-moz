<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notificacao extends Model
{
    use HasFactory;

    protected $table = 'notificacoes';

    protected $fillable = [
        'uuid',
        'user_id',
        'titulo',
        'mensagem',
        'tipo',
        'link',
        'lida',
        'data_leitura',
    ];

    protected $casts = [
        'lida' => 'boolean',
        'data_leitura' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notificacao) {
            if (empty($notificacao->uuid)) {
                $notificacao->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function marcarComoLida()
    {
        $this->lida = true;
        $this->data_leitura = now();
        $this->save();
    }
}