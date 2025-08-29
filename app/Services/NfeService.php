<?php
// app/Services/NfeService.php

namespace App\Services;

use App\Models\Nfe;
use App\Models\Empresa;
use NFePHP\NFe\Tools;
use NFePHP\NFe\Make;
use NFePHP\Common\Certificate;
use NFePHP\DA\NFe\Danfe;
use Illuminate\Support\Facades\Storage;

class NfeService
{
    protected $tools;
    protected $empresa;

    public function __construct(Empresa $empresa)
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
            "versao" => '4.00',
            "tokenIBPT" => "",
            "CSC" => "",
            "CSCid" => ""
        ];

        $certificadoPath = Storage::path($this->empresa->certificado_path);
        $certificado = Certificate::readPfx(
            file_get_contents($certificadoPath),
            $this->empresa->certificado_senha
        );

        $this->tools = new Tools(json_encode($config), $certificado);
        $this->tools->model('55'); // NFe
    }

    public function criarNfe(Nfe $nfe)
    {
        try {
            $make = new Make();
            
            // Identificação
            $std = new \stdClass();
            $std->versao = '4.00';
            $std->Id = null;
            $std->pk_nItem = '';
            $make->taginfNFe($std);

            // IDE
            $std = new \stdClass();
            $std->cUF = $this->empresa->uf === 'SP' ? 35 : 35; // Ajustar conforme UF
            $std->cNF = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            $std->natOp = 'Venda';
            $std->mod = $nfe->tipo === 'nfce' ? 65 : 55;
            $std->serie = $nfe->serie;
            $std->nNF = $nfe->numero;
            $std->dhEmi = $nfe->data_emissao->format('Y-m-d\TH:i:sP');
            $std->dhSaiEnt = $nfe->data_saida ? $nfe->data_saida->format('Y-m-d\TH:i:sP') : null;
            $std->tpNF = 1; // 0=Entrada, 1=Saída
            $std->idDest = 1; // 1=Operação interna
            $std->cMunFG = $this->empresa->cod_municipio;
            $std->tpImp = 1; // 1=DANFE normal
            $std->tpEmis = 1; // 1=Emissão normal
            $std->cDV = 0; // Será calculado
            $std->tpAmb = $this->empresa->ambiente;
            $std->finNFe = 1; // 1=NF-e normal
            $std->indFinal = 1; // 1=Consumidor final
            $std->indPres = 1; // 1=Operação presencial
            $std->procEmi = 0; // 0=Emissão de NF-e com aplicativo do contribuinte
            $std->verProc = '1.0';
            $make->tagide($std);

            // Emitente
            $std = new \stdClass();
            $std->xNome = $this->empresa->razao_social;
            $std->xFant = $this->empresa->nome_fantasia;
            $std->IE = $this->empresa->ie;
            $std->IM = $this->empresa->im;
            $std->CNAE = '';
            $std->CRT = $this->empresa->crt;
            $make->tagemit($std);

            // Endereço do emitente
            $std = new \stdClass();
            $std->xLgr = $this->empresa->logradouro;
            $std->nro = $this->empresa->numero;
            $std->xCpl = $this->empresa->complemento;
            $std->xBairro = $this->empresa->bairro;
            $std->cMun = $this->empresa->cod_municipio;
            $std->xMun = $this->empresa->cidade;
            $std->UF = $this->empresa->uf;
            $std->CEP = $this->empresa->cep;
            $std->cPais = 1058;
            $std->xPais = 'Brasil';
            $std->fone = $this->empresa->telefone;
            $make->tagenderEmit($std);

            // Destinatário
            $cliente = $nfe->cliente;
            if ($cliente->documento) {
                $std = new \stdClass();
                $std->xNome = $cliente->nome;
                $std->indIEDest = $cliente->ie ? 1 : 9; // 1=Contribuinte ICMS, 9=Não contribuinte
                $std->IE = $cliente->ie;
                $std->email = $cliente->email;
                
                if (strlen($cliente->documento) === 11) {
                    $std->CPF = $cliente->documento;
                } else {
                    $std->CNPJ = $cliente->documento;
                }
                
                $make->tagdest($std);

                // Endereço do destinatário
                if ($cliente->logradouro) {
                    $std = new \stdClass();
                    $std->xLgr = $cliente->logradouro;
                    $std->nro = $cliente->numero;
                    $std->xCpl = $cliente->complemento;
                    $std->xBairro = $cliente->bairro;
                    $std->cMun = $cliente->cod_municipio;
                    $std->xMun = $cliente->cidade;
                    $std->UF = $cliente->uf;
                    $std->CEP = $cliente->cep;
                    $std->cPais = 1058;
                    $std->xPais = 'Brasil';
                    $std->fone = $cliente->telefone;
                    $make->tagenderDest($std);
                }
            }

            // Itens
            $itemNumber = 1;
            foreach ($nfe->itens as $item) {
                $produto = $item->produto;
                
                // Produto
                $std = new \stdClass();
                $std->item = $itemNumber;
                $std->cProd = $produto->codigo;
                $std->cEAN = '';
                $std->xProd = $produto->nome;
                $std->NCM = $produto->ncm;
                $std->CEST = $produto->cest;
                $std->CFOP = $item->cfop;
                $std->uCom = $produto->unidade;
                $std->qCom = $item->quantidade;
                $std->vUnCom = $item->valor_unitario;
                $std->vProd = $item->valor_total;
                $std->cEANTrib = '';
                $std->uTrib = $produto->unidade;
                $std->qTrib = $item->quantidade;
                $std->vUnTrib = $item->valor_unitario;
                $std->indTot = 1;
                $make->tagprod($std);

                // Impostos
                $std = new \stdClass();
                $std->item = $itemNumber;
                $make->tagimposto($std);

                // ICMS
                $std = new \stdClass();
                $std->item = $itemNumber;
                $std->orig = 0;
                $std->CST = '102'; // Tributada pelo Simples Nacional sem permissão de crédito
                $make->tagICMSSN102($std);

                // PIS
                $std = new \stdClass();
                $std->item = $itemNumber;
                $std->CST = '07'; // Operação Isenta da Contribuição
                $make->tagPISOutr($std);

                // COFINS
                $std = new \stdClass();
                $std->item = $itemNumber;
                $std->CST = '07'; // Operação Isenta da Contribuição
                $make->tagCOFINSOutr($std);

                $itemNumber++;
            }

            // Total
            $std = new \stdClass();
            $std->vBC = 0;
            $std->vICMS = 0;
            $std->vICMSDeson = 0;
            $std->vFCP = 0;
            $std->vBCST = 0;
            $std->vST = 0;
            $std->vFCPST = 0;
            $std->vFCPSTRet = 0;
            $std->vProd = $nfe->valor_total;
            $std->vFrete = 0;
            $std->vSeg = 0;
            $std->vDesc = 0;
            $std->vII = 0;
            $std->vIPI = 0;
            $std->vIPIDevol = 0;
            $std->vPIS = 0;
            $std->vCOFINS = 0;
            $std->vOutro = 0;
            $std->vNF = $nfe->valor_total;
            $make->tagICMSTot($std);

            // Transporte
            $std = new \stdClass();
            $std->modFrete = 9; // 9=Sem Ocorrência de Transporte
            $make->tagtransp($std);

            // Pagamento (para NFCe)
            if ($nfe->tipo === 'nfce') {
                $std = new \stdClass();
                $std->vTroco = 0;
                $make->tagpag($std);

                $std = new \stdClass();
                $std->indPag = 0; // 0=Pagamento à Vista
                $std->tPag = '01'; // 01=Dinheiro
                $std->vPag = $nfe->valor_total;
                $make->tagdetPag($std);
            }

            // Informações adicionais
            if ($nfe->observacoes) {
                $std = new \stdClass();
                $std->infCpl = $nfe->observacoes;
                $make->taginfAdic($std);
            }

            $xml = $make->getXML();
            
            // Assinar o XML
            $xmlAssinado = $this->tools->signNFe($xml);
            
            return $xmlAssinado;

        } catch (\Exception $e) {
            throw new \Exception("Erro ao criar NFe: " . $e->getMessage());
        }
    }

    public function enviarNfe($xml)
    {
        try {
            $idLote = str_pad(time(), 15, '0', STR_PAD_LEFT);
            $resp = $this->tools->sefazEnviaLote([$xml], $idLote);
            
            $st = new \NFePHP\NFe\Complements\NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($resp);
            
            if ($std->cStat == 103) {
                // Lote recebido com sucesso, consultar protocolo
                return $this->consultarProtocolo($std->infRec->nRec);
            }
            
            return $std;
            
        } catch (\Exception $e) {
            throw new \Exception("Erro ao enviar NFe: " . $e->getMessage());
        }
    }

    public function consultarProtocolo($recibo)
    {
        try {
            $resp = $this->tools->sefazConsultaRecibo($recibo);
            $st = new \NFePHP\NFe\Common\Standardize();
            return $st->toStd($resp);
            
        } catch (\Exception $e) {
            throw new \Exception("Erro ao consultar protocolo: " . $e->getMessage());
        }
    }

    public function gerarDanfe($xml, $logoPath = null)
    {
        try {
            $danfe = new Danfe($xml);
            
            if ($logoPath && file_exists($logoPath)) {
                $danfe->logoParameters($logoPath, 'C', true, false, '45', '45');
            }
            
            return $danfe->render();
            
        } catch (\Exception $e) {
            throw new \Exception("Erro ao gerar DANFE: " . $e->getMessage());
        }
    }
}