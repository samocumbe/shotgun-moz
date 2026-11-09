<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model SyncQueue
 * 
 * Representa a fila de sincronização para operações offline
 * 
 * @property int $id
 * @property string $uuid
 * @property int $dispositivo_id
 * @property string $tipo
 * @property string $entidade
 * @property int $entidade_id
 * @property array $payload
 * @property string $status
 * @property int $tentativas
 * @property string|null $synced_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read Dispositivo $dispositivo
 */
class SyncQueue extends Model
{
    use HasFactory;

    /**
     * Status possíveis para sincronização
     */
    const STATUS_PENDENTE = 'pendente';
    const STATUS_PROCESSANDO = 'processando';
    const STATUS_SUCESSO = 'sucesso';
    const STATUS_FALHA = 'falha';

    /**
     * Tipos de operações
     */
    const TIPO_CHECKIN = 'checkin';
    const TIPO_VENDA = 'venda';
    const TIPO_CANCELAMENTO = 'cancelamento';
    const TIPO_ATUALIZACAO = 'atualizacao';

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'uuid',
        'dispositivo_id',
        'tipo',
        'entidade',
        'entidade_id',
        'payload',
        'status',
        'tentativas',
        'synced_at',
    ];

    /**
     * Atributos que devem ser tratados como tipos específicos
     */
    protected $casts = [
        'payload' => 'array',
        'tentativas' => 'integer',
        'synced_at' => 'datetime',
    ];

    /**
     * Boot do modelo: gera UUID automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sync) {
            if (empty($sync->uuid)) {
                $sync->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relacionamento: SyncQueue pertence a um Dispositivo
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dispositivo()
    {
        return $this->belongsTo(Dispositivo::class);
    }

    /**
     * Verifica se a sincronização foi bem-sucedida
     * 
     * @return bool
     */
    public function isSucesso(): bool
    {
        return $this->status === self::STATUS_SUCESSO;
    }

    /**
     * Verifica se a sincronização está pendente
     * 
     * @return bool
     */
    public function isPendente(): bool
    {
        return $this->status === self::STATUS_PENDENTE;
    }

    /**
     * Marca como sucesso
     * 
     * @return void
     */
    public function marcarSucesso(): void
    {
        $this->status = self::STATUS_SUCESSO;
        $this->synced_at = now();
        $this->save();
    }

    /**
     * Marca como falha e incrementa tentativas
     * 
     * @return void
     */
    public function marcarFalha(): void
    {
        $this->status = self::STATUS_FALHA;
        $this->increment('tentativas');
        $this->save();
    }

    /**
     * Incrementa tentativas e mantém pendente
     * 
     * @return void
     */
    public function incrementarTentativas(): void
    {
        $this->increment('tentativas');
    }

    /**
     * Escopo: Filtrar operações pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    /**
     * Escopo: Filtrar operações com falha
     */
    public function scopeComFalha($query)
    {
        return $query->where('status', self::STATUS_FALHA);
    }

    /**
     * Escopo: Filtrar por dispositivo
     */
    public function scopePorDispositivo($query, $dispositivoId)
    {
        return $query->where('dispositivo_id', $dispositivoId);
    }
}