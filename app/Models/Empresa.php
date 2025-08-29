<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    /** @use HasFactory<\Database\Factories\EmpresaFactory> */
    use HasFactory;
    protected $fillable = [
        'razao_social', 'nome_fantasia', 'cnpj', 'ie', 'im',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro',
        'cidade', 'uf', 'cod_municipio', 'telefone', 'email',
        'crt', 'certificado_path', 'certificado_senha',
        'serie_nfe', 'serie_nfce', 'numero_nfe', 'numero_nfce', 'ambiente'
    ];

    public function nfes()
    {
        return $this->hasMany(Nfe::class);
    }

    public function proximoNumeroNfe()
    {
        $this->increment('numero_nfe');
        return $this->numero_nfe;
    }

    public function proximoNumeroNfce()
    {
        $this->increment('numero_nfce');
        return $this->numero_nfce;
    }
}
