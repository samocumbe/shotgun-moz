<?php

/**
 * ============================================================
 * MODEL: Auditoria
 * ============================================================
 * 
 * Representa o registro de todas as ações realizadas no sistema.
 * 
 * Campos:
 * - id: Chave primária
 * - uuid: Identificador único
 * - user_id: Usuário que realizou a ação
 * - organizacao_id: Organização do usuário
 * - acao: Tipo de ação (create, update, delete, login, etc)
 * - tabela: Tabela afetada
 * - registro_id: ID do registro afetado
 * - dados_anteriores: Dados antes da alteração (JSON)
 * - dados_novos: Dados depois da alteração (JSON)
 * - ip: IP do usuário
 * - user_agent: Navegador/agente do usuário
 * - rota: Rota acessada
 * - metodo: Método HTTP
 * - created_at: Data/hora da ação
 * - updated_at: Data/hora da atualização
 * 
 * Relacionamentos:
 * - belongsTo: User
 * - belongsTo: Organizacao
 * 
 * Scopes:
 * - porUsuario(), porOrganizacao(), porAcao(), porTabela()
 * - entreDatas()
 * 
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';

    protected $fillable = [
        'uuid',
        'user_id',
        'organizacao_id',
        'acao',
        'tabela',
        'registro_id',
        'dados_anteriores',
        'dados_novos',
        'ip',
        'user_agent',
        'rota',
        'metodo',
    ];

    protected $casts = [
        'dados_anteriores' => 'array',
        'dados_novos' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($auditoria) {
            if (empty($auditoria->uuid)) {
                $auditoria->uuid = (string) Str::uuid();
            }
        });
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }

    // ============================================================
    // SCOPES (Filtros)
    // ============================================================

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorOrganizacao($query, $organizacaoId)
    {
        return $query->where('organizacao_id', $organizacaoId);
    }

    public function scopePorAcao($query, $acao)
    {
        return $query->where('acao', $acao);
    }

    public function scopePorTabela($query, $tabela)
    {
        return $query->where('tabela', $tabela);
    }

    public function scopeEntreDatas($query, $inicio, $fim)
    {
        return $query->whereBetween('created_at', [$inicio, $fim]);
    }

    // ============================================================
    // MÉTODOS DE FORMATAÇÃO
    // ============================================================

    public function getAcaoLabelAttribute(): string
    {
        return match($this->acao) {
            'create' => 'Criação',
            'update' => 'Atualização',
            'delete' => 'Exclusão',
            'login' => 'Login',
            'logout' => 'Logout',
            'import' => 'Importação',
            'export' => 'Exportação',
            'publish' => 'Publicação',
            'cancel' => 'Cancelamento',
            'restore' => 'Restauração',
            default => ucfirst($this->acao)
        };
    }

    public function getAcaoIconAttribute(): string
    {
        return match($this->acao) {
            'create' => 'bi-plus-circle text-success',
            'update' => 'bi-pencil-square text-warning',
            'delete' => 'bi-trash text-danger',
            'login' => 'bi-box-arrow-in-right text-info',
            'logout' => 'bi-box-arrow-right text-secondary',
            'import' => 'bi-file-earmark-arrow-up text-primary',
            'export' => 'bi-file-earmark-arrow-down text-primary',
            'publish' => 'bi-check-circle text-success',
            'cancel' => 'bi-x-circle text-danger',
            'restore' => 'bi-arrow-counterclockwise text-info',
            default => 'bi-dot'
        };
    }
}