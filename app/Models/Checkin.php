<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model Checkin
 * 
 * Representa o registro de entrada/validação de um ingresso
 * 
 * @property int $id
 * @property string $uuid
 * @property int $ingresso_id
 * @property int $evento_id
 * @property int|null $dispositivo_id
 * @property string $qr_code
 * @property string $data_checkin
 * @property string|null $porta_acesso
 * @property string|null $localizacao
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read Ingresso $ingresso
 * @property-read Evento $evento
 * @property-read Dispositivo|null $dispositivo
 */
class Checkin extends Model
{
    use HasFactory;

    /**
     * Status possíveis para um checkin
     */
    const STATUS_PENDENTE = 'pendente';
    const STATUS_SINCRONIZADO = 'sincronizado';

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'uuid',
        'ingresso_id',
        'evento_id',
        'dispositivo_id',
        'qr_code',
        'data_checkin',
        'porta_acesso',
        'localizacao',
        'status',
    ];

    /**
     * Atributos que devem ser tratados como tipos específicos
     */
    protected $casts = [
        'data_checkin' => 'datetime',
    ];

    /**
     * Boot do modelo: gera UUID automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($checkin) {
            if (empty($checkin->uuid)) {
                $checkin->uuid = (string) Str::uuid();
            }
            
            // Definir data do checkin se não for informada
            if (empty($checkin->data_checkin)) {
                $checkin->data_checkin = now();
            }
        });
    }

    /**
     * Relacionamento: Checkin pertence a um Ingresso
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ingresso()
    {
        return $this->belongsTo(Ingresso::class);
    }

    /**
     * Relacionamento: Checkin pertence a um Evento
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Relacionamento: Checkin pertence a um Dispositivo
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dispositivo()
    {
        return $this->belongsTo(Dispositivo::class);
    }

    /**
     * Verifica se o checkin já foi sincronizado
     * 
     * @return bool
     */
    public function isSincronizado(): bool
    {
        return $this->status === self::STATUS_SINCRONIZADO;
    }

    /**
     * Marca o checkin como sincronizado
     * 
     * @return void
     */
    public function marcarComoSincronizado(): void
    {
        $this->status = self::STATUS_SINCRONIZADO;
        $this->save();
    }

    /**
     * Escopo: Filtrar checkins pendentes de sincronização
     */
    public function scopePendentes($query)
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    /**
     * Escopo: Filtrar checkins de um evento específico
     */
    public function scopePorEvento($query, $eventoId)
    {
        return $query->where('evento_id', $eventoId);
    }
}