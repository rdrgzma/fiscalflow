<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nfe extends Model
{
    /** @use HasFactory<\Database\Factories\NfeFactory> */
    use HasFactory;
    protected $table = 'nfes';
    protected $fillable = [
        'empresa_id', 'cliente_id', 'tipo', 'serie', 'numero',
        'chave', 'status', 'data_emissao', 'data_saida',
        'valor_total', 'observacoes', 'dados_xml',
        'protocolo', 'mensagem_retorno', 'xml_path', 'pdf_path'
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'data_saida' => 'datetime',
        'valor_total' => 'decimal:2',
        'dados_xml' => 'array'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens()
    {
        return $this->hasMany(NfeItem::class);
    }

    public function getNumeroFormatadoAttribute()
    {
        return str_pad($this->numero, 9, '0', STR_PAD_LEFT);
    }
}
