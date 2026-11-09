<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $table = 'sms_templates';

    /**
     * Canais de envio (coluna `tipo`)
     */
    const TIPO_SMS      = 'sms';
    const TIPO_WHATSAPP = 'whatsapp';

    const TIPOS = [
        self::TIPO_SMS      => 'SMS',
        self::TIPO_WHATSAPP => 'WhatsApp',
    ];

    /**
     * Categorias de negócio (coluna `categoria`)
     */
    const CATEGORIA_VENDA    = 'venda';
    const CATEGORIA_LEMBRETE = 'lembrete';
    const CATEGORIA_PROMOCAO = 'promocao';
    const CATEGORIA_EVENTO   = 'evento';
    const CATEGORIA_OUTRO    = 'outro';

    const CATEGORIAS = [
        self::CATEGORIA_VENDA    => 'Venda',
        self::CATEGORIA_LEMBRETE => 'Lembrete',
        self::CATEGORIA_PROMOCAO => 'Promoção',
        self::CATEGORIA_EVENTO   => 'Evento',
        self::CATEGORIA_OUTRO    => 'Outro',
    ];

    protected $fillable = [
        'uuid',
        'organizacao_id',
        'nome',
        'assunto',
        'categoria',   // 🆕 novo campo
        'mensagem',
        'tipo',        // canal: sms ou whatsapp
        'variaveis',
        'is_active',
    ];

    protected $casts = [
        'variaveis' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->uuid)) {
                $template->uuid = (string) Str::uuid();
            }
            if (empty($template->tipo)) {
                $template->tipo = self::TIPO_SMS;
            }
            if (empty($template->categoria)) {
                $template->categoria = self::CATEGORIA_OUTRO;
            }
            if (!isset($template->is_active)) {
                $template->is_active = true;
            }
        });
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }

    public function sms()
    {
        return $this->hasMany(Sms::class, 'template_id');
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    public static function getTipos(): array
    {
        return self::TIPOS;
    }

    public static function getCategorias(): array
    {
        return self::CATEGORIAS;
    }

    /**
     * Extrai variáveis {nome} ou {{nome}} de um texto
     */
    public static function extrairVariaveisDe(string $mensagem): array
    {
        $vars = [];

        // {{variavel}}
        if (preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $mensagem, $m)) {
            $vars = array_merge($vars, $m[1]);
        }

        // {variavel} (removendo antes o que já foi apanhado com {{ }})
        $semDuplas = preg_replace('/\{\{\s*[a-zA-Z0-9_]+\s*\}\}/', '', $mensagem);
        if (preg_match_all('/\{\s*([a-zA-Z0-9_]+)\s*\}/', $semDuplas, $m)) {
            $vars = array_merge($vars, $m[1]);
        }

        return array_values(array_unique($vars));
    }

    public function extrairVariaveis(): array
    {
        return $this->variaveis ?? self::extrairVariaveisDe($this->mensagem);
    }

    /**
     * Renderiza a mensagem substituindo as variáveis
     */
    public function renderizar(array $dados = []): string
    {
        $mensagem = $this->mensagem;

        foreach ($dados as $chave => $valor) {
            $mensagem = str_replace('{{' . $chave . '}}', $valor, $mensagem);
            $mensagem = str_replace('{' . $chave . '}', $valor, $mensagem);
        }

        return $mensagem;
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeAtivos($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }
}