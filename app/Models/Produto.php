<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    /** @use HasFactory<\Database\Factories\ProdutoFactory> */
    use HasFactory;

    protected $fillable = [
        'codigo', 'nome', 'descricao', 'ncm', 'cfop',
        'unidade', 'valor_unitario', 'estoque',
        'cest', 'peso_bruto', 'peso_liquido'
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'estoque' => 'decimal:3',
        'peso_bruto' => 'decimal:3',
        'peso_liquido' => 'decimal:3',
    ];

 
}
