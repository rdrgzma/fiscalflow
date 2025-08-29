<?php
// app/Services/NfeConsultaService.php

namespace App\Services;

use App\Models\Nfe;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\Storage;

class NfeConsultaService
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

    public function consultarNfe(Nfe $nfe)
    {
        if (!$nfe->chave) {
            throw new \Exception("NFe não possui chave de acesso para consulta");
        }

        try {
            $response = $this->tools->sefazConsultaChave($nfe->chave);
            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($response);

            $statusAtual = $this->interpretarStatus($std->cStat);
            
            // Atualizar status local se necessário
            if ($statusAtual !== $nfe->status) {
                $nfe->update([
                    'status' => $statusAtual,
                    'mensagem_retorno' => $std->xMotivo ?? 'Status atualizado via consulta'
                ]);
            }

            return [
                'success' => true,
                'status' => $statusAtual,
                'codigo' => $std->cStat,
                'motivo' => $std->xMotivo ?? '',
                'protocolo' => $std->protNFe->infProt->nProt ?? null,
                'data_autorizacao' => $std->protNFe->infProt->dhRecbto ?? null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function consultarStatusServico()
    {
        try {
            $response = $this->tools->sefazStatus();
            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($response);

            return [
                'success' => true,
                'status' => $std->cStat == 107 ? 'online' : 'offline',
                'codigo' => $std->cStat,
                'motivo' => $std->xMotivo ?? '',
                'tempo_medio' => $std->tMed ?? 0
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'erro',
                'error' => $e->getMessage()
            ];
        }
    }

    protected function interpretarStatus($cStat)
    {
        $statusMap = [
            100 => 'autorizada',
            110 => 'denegada',
            135 => 'cancelada',
            301 => 'irregular',
            302 => 'denegada'
        ];

        return $statusMap[$cStat] ?? 'pendente';
    }
}