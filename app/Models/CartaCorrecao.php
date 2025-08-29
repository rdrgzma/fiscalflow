<?php
// app/Models/CartaCorrecao.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartaCorrecao extends Model
{
    use HasFactory;

    protected $table = 'cartas_correcao';

    protected $fillable = [
        'nfe_id', 'sequencial', 'correcao', 'protocolo',
        'data_evento', 'status', 'xml_path'
    ];

    protected $casts = [
        'data_evento' => 'datetime'
    ];

    public function nfe()
    {
        return $this->belongsTo(Nfe::class);
    }
}