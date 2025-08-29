<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory;
    protected $fillable = [
        'nome', 'email', 'telefone', 'documento', 'ie',
        'cep', 'logradouro', 'numero', 'complemento',
        'bairro', 'cidade', 'uf', 'cod_municipio'
    ];

    public function nfes()
    {
        return $this->hasMany(Nfe::class);
    }

    public function getTipoDocumentoAttribute()
    {
        return strlen($this->documento) === 11 ? 'CPF' : 'CNPJ';
    }
}
