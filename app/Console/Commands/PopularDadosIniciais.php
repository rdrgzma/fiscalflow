<?php
// app/Console/Commands/PopularDadosIniciais.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\Produto;

class PopularDadosIniciais extends Command
{
    protected $signature = 'nfe:popular-dados';
    protected $description = 'Popula dados iniciais para teste do sistema NFe';

    public function handle()
    {
        // Criar empresa
        $empresa = Empresa::create([
            'razao_social' => 'Empresa Teste LTDA',
            'nome_fantasia' => 'Empresa Teste',
            'cnpj' => '00000000000100',
            'ie' => '123456789',
            'cep' => '01310100',
            'logradouro' => 'Avenida Paulista',
            'numero' => '1000',
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'cod_municipio' => 3550308,
            'telefone' => '11999999999',
            'email' => 'contato@empresateste.com.br',
            'crt' => 1,
            'certificado_path' => 'certificados/teste.pfx',
            'certificado_senha' => 'senha123',
            'ambiente' => 2
        ]);

        // Criar clientes
        Cliente::create([
            'nome' => 'Cliente Consumidor Final',
            'documento' => '00000000191',
            'email' => 'cliente@email.com'
        ]);

        Cliente::create([
            'nome' => 'Empresa Cliente LTDA',
            'documento' => '00000000000200',
            'ie' => '987654321',
            'email' => 'empresa@cliente.com.br',
            'cep' => '04038001',
            'logradouro' => 'Rua Vergueiro',
            'numero' => '1000',
            'bairro' => 'Liberdade',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'cod_municipio' => 3550308
        ]);

        // Criar produtos
        Produto::create([
            'codigo' => 'PROD001',
            'nome' => 'Produto Teste 1',
            'descricao' => 'Descrição do produto teste 1',
            'ncm' => '12345678',
            'cfop' => '5102',
            'unidade' => 'UN',
            'valor_unitario' => 10.50
        ]);

        Produto::create([
            'codigo' => 'PROD002',
            'nome' => 'Produto Teste 2',
            'descricao' => 'Descrição do produto teste 2',
            'ncm' => '87654321',
            'cfop' => '5102',
            'unidade' => 'KG',
            'valor_unitario' => 25.75
        ]);

        $this->info('Dados iniciais populados com sucesso!');
    }
}
