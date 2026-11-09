<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model Dispositivo
 * 
 * Representa dispositivos (smartphones/tablets) usados para scanner/POS
 * 
 * @property int $id
 * @property string $uuid
 * @property string $nome
 * @property string $tipo
 * @property int|null $evento_id
 * @property int $user_id
 * @property string|null $ultimo_sync
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read Evento|null $evento
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|Checkin[] $checkins
 * @property-read \Illuminate\Database\Eloquent\Collection|SyncQueue[] $syncQueues
 */
class Dispositivo extends Model
{
    use HasFactory;

    /**
     * Tipos de dispositivos possíveis
     */
    const TIPO_SCANNER = 'scanner';
    const TIPO_POS = 'pos';
    const TIPO_SUPERVISOR = 'supervisor';
    const TIPO_ADMIN = 'admin';

    /**
     * Status possíveis
     */
    const STATUS_ATIVO = 'ativo';
    const STATUS_INATIVO = 'inativo';

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'uuid',
        'nome',
        'tipo',
        'evento_id',
        'user_id',
        'ultimo_sync',
        'status',
    ];

    /**
     * Atributos que devem ser tratados como tipos específicos
     */
    protected $casts = [
        'ultimo_sync' => 'datetime',
    ];

    /**
     * Boot do modelo: gera UUID automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dispositivo) {
            if (empty($dispositivo->uuid)) {
                $dispositivo->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relacionamento: Dispositivo pertence a um Evento
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Relacionamento: Dispositivo pertence a um Usuário
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Relacionamento: Dispositivo tem muitos Checkins
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Relacionamento: Dispositivo tem muitos SyncQueue
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function syncQueues()
    {
        return $this->hasMany(SyncQueue::class);
    }

    /**
     * Verifica se o dispositivo está ativo
     * 
     * @return bool
     */
    public function isAtivo(): bool
    {
        return $this->status === self::STATUS_ATIVO;
    }

    /**
     * Verifica se o dispositivo é um scanner
     * 
     * @return bool
     */
    public function isScanner(): bool
    {
        return $this->tipo === self::TIPO_SCANNER;
    }

    /**
     * Atualiza a data do último sync
     * 
     * @return void
     */
    public function atualizarUltimoSync(): void
    {
        $this->ultimo_sync = now();
        $this->save();
    }

    /**
     * Escopo: Filtrar dispositivos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    /**
     * Escopo: Filtrar por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Escopo: Filtrar por evento
     */
    public function scopePorEvento($query, $eventoId)
    {
        return $query->where('evento_id', $eventoId);
    }
}