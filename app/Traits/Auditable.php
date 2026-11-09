<?php

/**
 * ============================================================
 * TRAIT: Auditable
 * ============================================================
 * 
 * Este trait deve ser usado em Models que precisam ser auditados.
 * Ele registra automaticamente as ações de criação, atualização e exclusão.
 * 
 * Uso: Adicione `use Auditable;` no Model.
 * 
 * Exemplo:
 * class Evento extends Model {
 *     use Auditable;
 * }
 * 
 * ============================================================
 */

namespace App\Traits;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot do trait: registra eventos do Model
     */
    protected static function bootAuditable()
    {
        // Registrar criação
        static::created(function ($model) {
            $model->registrarAuditoria('create', null, $model->getAttributes());
        });

        // Registrar atualização
        static::updated(function ($model) {
            $model->registrarAuditoria('update', $model->getOriginal(), $model->getAttributes());
        });

        // Registrar exclusão (soft delete)
        static::deleting(function ($model) {
            if ($model->isForceDeleting() || !method_exists($model, 'isForceDeleting')) {
                $model->registrarAuditoria('delete', $model->getAttributes(), null);
            }
        });

        // Registrar restauração (se usar soft delete)
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->registrarAuditoria('restore', null, $model->getAttributes());
            });
        }
    }

    /**
     * Registrar ação na auditoria
     */
    protected function registrarAuditoria($acao, $dadosAnteriores, $dadosNovos)
    {
        try {
            $user = Auth::user();
            $organizacaoId = $user ? $user->organizacao_id : null;

            // Remover campos sensíveis
            if ($dadosAnteriores) {
                unset($dadosAnteriores['password']);
                unset($dadosAnteriores['remember_token']);
            }
            if ($dadosNovos) {
                unset($dadosNovos['password']);
                unset($dadosNovos['remember_token']);
            }

            Auditoria::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $user ? $user->id : null,
                'organizacao_id' => $organizacaoId,
                'acao' => $acao,
                'tabela' => $this->getTable(),
                'registro_id' => $this->id,
                'dados_anteriores' => $dadosAnteriores,
                'dados_novos' => $dadosNovos,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'rota' => request()->route() ? request()->route()->getName() : null,
                'metodo' => request()->method(),
            ]);
        } catch (\Exception $e) {
            // Não deixar que erros de auditoria interrompam a aplicação
            \Log::error('Erro ao registrar auditoria: ' . $e->getMessage());
        }
    }
}