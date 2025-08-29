<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NfeItem extends Model
{
    /** @use HasFactory<\Database\Factories\NfeItemFactory> */
    use HasFactory;
    protected $table = 'nfe_items';
    protected $fillable = [
        'nfe_id', 'produto_id', 'item', 'quantidade',
        'valor_unitario', 'valor_total', 'cfop'
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2'
    ];

    public function nfe()
    {
        return $this->belongsTo(Nfe::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
