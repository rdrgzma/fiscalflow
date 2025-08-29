<?php
// app/Services/CartaCorrecaoService.php

namespace App\Services;

use App\Models\Nfe;
use App\Models\CartaCorrecao;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\Storage;

class CartaCorrecaoService
{
    protected $tools;
    protected $empresa;

    public function __construct($empresa)
    {
        $this->empresa = $empresa;
        $this->setupTools();
    }

    protected function setupTools()
    {
        $config = [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => $this->empresa->ambiente,
            "razaosocial" => $this->empresa->razao_social,
            "cnpj" => $this->empresa->cnpj,
            "siglaUF" => $this->empresa->uf,
            "schemes" => "PL_009_V4",
            "versao" => '4.00'
        ];

        $certificadoPath = Storage::path($this->empresa->certificado_path);
        $certificado = Certificate::readPfx(
            file_get_contents($certificadoPath),
            $this->empresa->certificado_senha
        );

        $this->tools = new Tools(json_encode($config), $certificado);
    }

    public function enviarCartaCorrecao(Nfe $nfe, $correcao)
    {
        if ($nfe->status !== 'autorizada') {
            throw new \Exception("Apenas NFes autorizadas podem receber carta de correção");
        }

        $sequencial = CartaCorrecao::where('nfe_id', $nfe->id)->count() + 1;

        try {
            $response = $this->tools->sefazCCe(
                $nfe->chave,
                $correcao,
                $sequencial
            );

            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($response);

            if ($std->cStat == 135) {
                // CCe autorizada
                $cartaCorrecao = CartaCorrecao::create([
                    'nfe_id' => $nfe->id,
                    'sequencial' => $sequencial,
                    'correcao' => $correcao,
                    'protocolo' => $std->retEvento->infEvento->nProt ?? null,
                    'data_evento' => now(),
                    'status' => 'autorizada'
                ]);

                // Salvar XML do evento
                $xmlPath = "nfes/cce_{$nfe->tipo}_{$nfe->numero}_{$sequencial}.xml";
                Storage::put($xmlPath, $response);
                $cartaCorrecao->update(['xml_path' => $xmlPath]);

                return [
                    'success' => true,
                    'message' => 'Carta de correção registrada com sucesso',
                    'protocolo' => $std->retEvento->infEvento->nProt ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $std->xMotivo ?? 'Erro na carta de correção',
                    'codigo' => $std->cStat
                ];
            }

        } catch (\Exception $e) {
            throw new \Exception("Erro ao enviar carta de correção: " . $e->getMessage());
        }
    }
}