<?php

/**
 * ============================================================
 * MODEL: User
 * ============================================================
 * 
 * Representa os usuários do sistema.
 * 
 * Regras de Acesso (RBAC):
 * - Admin: Acesso total.
 * - Operador: Acesso operacional (gere a sua organização).
 * - Promoter: Acesso limitado (vende e vê apenas o que vendeu).
 * - Cliente: Acesso restrito (vê apenas o que comprou).
 * 
 * ============================================================
 */

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Auditable;

    // ============================================================
    // CONSTANTES DE PAPÉIS (ROLES)
    // ============================================================

    const ROLE_ADMIN = 'admin';
    const ROLE_OPERADOR = 'operador';
    const ROLE_PROMOTER = 'promoter';
    const ROLE_CLIENTE = 'cliente';

    const ROLES = [
        self::ROLE_ADMIN => 'Administrador',
        self::ROLE_OPERADOR => 'Operador',
        self::ROLE_PROMOTER => 'Promoter',
        self::ROLE_CLIENTE => 'Cliente',
    ];

    // ============================================================
    // CONFIGURAÇÃO DO MODEL
    // ============================================================

    protected $fillable = [
        'organizacao_id',
        'name',
        'email',
        'password',
        'role',
        'is_admin',
        'is_organizador',
        'telefone',
        'nuit',
        'endereco',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_organizador' => 'boolean',
        ];
    }

    // ============================================================
    // RELACIONAMENTOS
    // ============================================================

    public function dispositivos()
    {
        return $this->hasMany(Dispositivo::class);
    }

    public function carteira()
    {
        return $this->hasMany(CarteiraIngresso::class);
    }

    public function ingressos()
    {
        return $this->hasManyThrough(Ingresso::class, CarteiraIngresso::class, 'user_id', 'id', 'id', 'ingresso_id');
    }

    public function participante()
    {
        return $this->hasOne(Participante::class, 'email', 'email');
    }

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }

    // ============================================================
    // MÉTODOS DE VERIFICAÇÃO DE PAPEL (ROLE)
    // ============================================================

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN || $this->is_admin === true;
    }

    public function isOperador(): bool
    {
        return $this->role === self::ROLE_OPERADOR;
    }

    public function isPromoter(): bool
    {
        return $this->role === self::ROLE_PROMOTER;
    }

    public function isCliente(): bool
    {
        return $this->role === self::ROLE_CLIENTE;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAdminAccess(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_OPERADOR, self::ROLE_PROMOTER]);
    }

    // ============================================================
    // MÉTODOS DE MULTI-ORGANIZADOR
    // ============================================================

    public function isOrganizador(): bool
    {
        return $this->is_organizador === true || in_array($this->role, ['admin', 'operador']);
    }

    public function podeAcessarOrganizacao($organizacaoId): bool
    {
        if ($this->isAdmin()) return true;
        return $this->organizacao_id === $organizacaoId && $this->isOrganizador();
    }

    public function podeGerenciarEvento($evento): bool
    {
        if ($this->isAdmin()) return true;
        // Operador e Promoter só gerenciam eventos da sua organização
        return $this->organizacao_id === $evento->organizacao_id && $this->isOrganizador() || $this->isPromoter();
    }

    public function getOrganizacao()
    {
        return $this->organizacao;
    }

    public function pertenceAUmaOrganizacao(): bool
    {
        return !is_null($this->organizacao_id);
    }

    // ============================================================
    // MÉTODOS DE FORMATAÇÃO
    // ============================================================

    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function getRoleColorAttribute(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'danger',
            self::ROLE_OPERADOR => 'info',
            self::ROLE_PROMOTER => 'warning',
            self::ROLE_CLIENTE => 'secondary',
            default => 'secondary'
        };
    }

    public function getNomeOrganizacaoAttribute(): string
    {
        return $this->organizacao ? $this->organizacao->nome : 'Sem organização';
    }

    public function isAtivoNaOrganizacao(): bool
    {
        if (!$this->organizacao) return false;
        return $this->organizacao->isAtivo();
    }
}