<?php

/**
 * ============================================================
 * MODEL: Cupom (Desconto)
 * ============================================================
 * 
 * Em Moçambique, este modelo representa um "Desconto" ou "Voucher".
 * Mantivemos o nome Cupom para compatibilidade com o código,
 * mas a interface usa "Desconto".
 * 
 * Representa descontos aplicáveis a compras de ingressos.
 * 
 * Campos:
 * - codigo: Código único do desconto
 * - evento_id: Evento específico (null = todos os eventos)
 * - nome: Nome descritivo do desconto
 * - tipo: percentual ou fixo
 * - valor: Valor do desconto
 * - valor_minimo: Valor mínimo da compra
 * - quantidade: Número máximo de usos
 * - usos: Quantidade de usos realizados
 * - validade_inicio: Data de início da validade
 * - validade_fim: Data de fim da validade
 * - ativo: Status do desconto
 * 
 * Relacionamentos:
 * - belongsTo: Evento
 * 
 * Métodos:
 * - isValid(): Verifica se o desconto é válido
 * - calcularDesconto(): Calcula o valor do desconto
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupom extends Model
{
    use HasFactory;

    /**
     * Nome da tabela no banco de dados
     */
    protected $table = 'cupons';

    /**
     * Atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'codigo',
        'evento_id',
        'nome',
        'tipo',
        'valor',
        'valor_minimo',
        'quantidade',
        'usos',
        'validade_inicio',
        'validade_fim',
        'ativo',
    ];

    /**
     * Atributos que devem ser tratados como tipos específicos
     */
    protected $casts = [
        'valor' => 'decimal:2',
        'valor_minimo' => 'decimal:2',
        'quantidade' => 'integer',
        'usos' => 'integer',
        'validade_inicio' => 'datetime',
        'validade_fim' => 'datetime',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento: Desconto pertence a um Evento
     * Se evento_id for null, o desconto aplica-se a todos os eventos
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * ============================================================
     * MÉTODO: Verifica se o desconto é válido
     * ============================================================
     * 
     * Verifica se o desconto pode ser utilizado com base em:
     * - Status ativo
     * - Data de validade (início e fim)
     * - Quantidade de usos disponíveis
     * 
     * @return bool
     */
    public function isValid()
    {
        // Verifica se está ativo
        if (!$this->ativo) {
            return false;
        }
        
        // Verifica data de início
        if ($this->validade_inicio && $this->validade_inicio > now()) {
            return false;
        }
        
        // Verifica data de fim
        if ($this->validade_fim && $this->validade_fim < now()) {
            return false;
        }
        
        // Verifica quantidade de usos disponíveis
        if ($this->quantidade && $this->usos >= $this->quantidade) {
            return false;
        }
        
        return true;
    }

    /**
     * ============================================================
     * MÉTODO: Calcula o valor do desconto
     * ============================================================
     * 
     * Calcula o desconto com base no tipo:
     * - percentual: Valor % do subtotal
     * - fixo: Valor fixo em MZN
     * 
     * @param float $subtotal
     * @return float
     */
    public function calcularDesconto($subtotal)
    {
        // Se o desconto não for válido, retorna 0
        if (!$this->isValid()) {
            return 0;
        }
        
        // Se o subtotal for menor que o valor mínimo, retorna 0
        if ($subtotal < $this->valor_minimo) {
            return 0;
        }

        // Calcula o desconto com base no tipo
        if ($this->tipo === 'percentual') {
            return ($subtotal * $this->valor) / 100;
        }

        // Tipo fixo: retorna o valor (limitado ao subtotal)
        return min($this->valor, $subtotal);
    }
}