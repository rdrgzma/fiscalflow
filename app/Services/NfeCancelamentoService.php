<?php
// app/Services/NfeCancelamentoService.php

namespace App\Services;

use App\Models\Nfe;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\Storage;

class NfeCancelamentoService
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

    public function cancelarNfe(Nfe $nfe, $justificativa)
    {
        if (strlen($justificativa) < 15) {
            throw new \Exception("Justificativa deve ter no mínimo 15 caracteres");
        }

        if ($nfe->status !== 'autorizada') {
            throw new \Exception("Apenas NFes autorizadas podem ser canceladas");
        }

        try {
            $response = $this->tools->sefazCancela(
                $nfe->chave,
                $justificativa,
                $nfe->protocolo
            );

            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($response);

            if ($std->cStat == 135) {
                // Cancelamento autorizado
                $nfe->update([
                    'status' => 'cancelada',
                    'mensagem_retorno' => 'NFe cancelada: ' . $justificativa
                ]);

                // Salvar XML do evento de cancelamento
                $xmlCancelamento = $response;
                $xmlCancelamentoPath = "nfes/cancelamento_{$nfe->tipo}_{$nfe->numero}.xml";
                Storage::put($xmlCancelamentoPath, $xmlCancelamento);

                return [
                    'success' => true,
                    'message' => 'NFe cancelada com sucesso',
                    'protocolo' => $std->retEvento->infEvento->nProt ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $std->xMotivo ?? 'Erro no cancelamento',
                    'codigo' => $std->cStat
                ];
            }

        } catch (\Exception $e) {
            throw new \Exception("Erro ao cancelar NFe: " . $e->getMessage());
        }
    }
}