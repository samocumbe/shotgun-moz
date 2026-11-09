<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    use HasFactory, Auditable;

    // 👇 FORÇAR O NOME DA TABELA CORRETO
    protected $table = 'locais';

    protected $fillable = [
        'nome',
        'endereco',
        'cidade',
        'provincia',
        'capacidade',
        'descricao',
    ];

    protected $casts = [
        'capacidade' => 'integer',
    ];

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }

    public function temCapacidadeDisponivel(int $quantidade): bool
    {
        return $this->capacidade >= $quantidade;
    }
}