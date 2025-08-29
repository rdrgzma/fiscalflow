<?php
// app/Models/NfeLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NfeLog extends Model
{
    use HasFactory;

    protected $table = 'nfe_logs';

    protected $fillable = [
        'nfe_id', 'usuario_id', 'acao', 'dados_antes', 
        'dados_depois', 'ip', 'user_agent', 'observacoes'
    ];

    protected $casts = [
        'dados_antes' => 'array',
        'dados_depois' => 'array'
    ];

    public function nfe()
    {
        return $this->belongsTo(Nfe::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}