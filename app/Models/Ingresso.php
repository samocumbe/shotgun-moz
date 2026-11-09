<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Model Ingresso
 * 
 * Representa um ingresso individual para um evento
 * 
 * @property int $id
 * @property string $uuid
 * @property string $qr_code
 * @property int $evento_id
 * @property int $categoria_ingresso_id
 * @property int|null $participante_id
 * @property float $preco_pago
 * @property string $status
 * @property string|null $data_compra
 * @property string|null $data_utilizacao
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read Evento $evento
 * @property-read CategoriaIngresso $categoria
 * @property-read Participante|null $participante
 * @property-read Checkin|null $checkin
 */
class Ingresso extends Model
{
    use HasFactory;

    /**
     * Status possíveis para um ingresso
     */
    const STATUS_DISPONIVEL = 'disponivel';
    const STATUS_RESERVADO = 'reservado';
    const STATUS_PAGO = 'pago';
    const STATUS_UTILIZADO = 'utilizado';
    const STATUS_CANCELADO = 'cancelado';
    const STATUS_REEMBOLSADO = 'reembolsado';

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'uuid',
        'qr_code',
        'evento_id',
        'categoria_ingresso_id',
        'participante_id',
        'preco_pago',
        'status',
        'data_compra',
        'data_utilizacao',
    ];

    /**
     * Atributos que devem ser tratados como tipos específicos
     */
    protected $casts = [
        'preco_pago' => 'decimal:2',
        'data_compra' => 'datetime',
        'data_utilizacao' => 'datetime',
    ];

    /**
     * Boot do modelo: gera UUID e QR Code automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ingresso) {
            // Gerar UUID único
            if (empty($ingresso->uuid)) {
                $ingresso->uuid = (string) Str::uuid();
            }
            
            // Gerar QR Code único (token criptograficamente seguro)
            if (empty($ingresso->qr_code)) {
                $ingresso->qr_code = 'EVT' . date('Y') . 
                                    '.' . strtoupper(Str::random(6)) . 
                                    '.' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Relacionamento: Ingresso pertence a um Evento
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Relacionamento: Ingresso pertence a uma Categoria
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoria()
    {
        return $this->belongsTo(CategoriaIngresso::class, 'categoria_ingresso_id');
    }

    /**
     * Relacionamento: Ingresso pertence a um Participante
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function participante()
    {
        return $this->belongsTo(Participante::class);
    }

    /**
     * Relacionamento: Ingresso tem um Checkin
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function checkin()
    {
        return $this->hasOne(Checkin::class);
    }

    /**
     * Verifica se o ingresso está pago
     * 
     * @return bool
     */
    public function isPago(): bool
    {
        return $this->status === self::STATUS_PAGO;
    }

    /**
     * Verifica se o ingresso já foi utilizado
     * 
     * @return bool
     */
    public function isUtilizado(): bool
    {
        return $this->status === self::STATUS_UTILIZADO;
    }

    /**
     * Verifica se o ingresso está cancelado
     * 
     * @return bool
     */
    public function isCancelado(): bool
    {
        return $this->status === self::STATUS_CANCELADO;
    }

    /**
     * Verifica se o ingresso pode ser utilizado (pago e não utilizado)
     * 
     * @return bool
     */
    public function podeSerUtilizado(): bool
    {
        return $this->isPago() && !$this->isUtilizado() && !$this->isCancelado();
    }

    /**
     * Marca o ingresso como utilizado
     * 
     * @return void
     */
    public function marcarComoUtilizado(): void
    {
        $this->status = self::STATUS_UTILIZADO;
        $this->data_utilizacao = now();
        $this->save();
    }

    /**
     * Gera e retorna o QR Code como SVG
     * 
     * @return string
     */
    public function gerarQRCode(): string
    {
        return QrCode::size(200)
                     ->format('svg')
                     ->generate($this->qr_code);
    }

    /**
     * Escopo: Filtrar ingressos disponíveis (pagos e não utilizados)
     */
    public function scopeDisponiveis($query)
    {
        return $query->where('status', self::STATUS_PAGO)
                     ->whereNull('data_utilizacao');
    }

    /**
     * Escopo: Filtrar ingressos de um evento específico
     */
    public function scopePorEvento($query, $eventoId)
    {
        return $query->where('evento_id', $eventoId);
    }
}