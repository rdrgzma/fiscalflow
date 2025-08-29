<?php
// app/Livewire/NfeEmissor.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Nfe;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Empresa;
use App\Models\NfeItem;
use App\Services\NfeService;
use Illuminate\Support\Facades\Storage;

class NfeEmissor extends Component
{
    public $empresa;
    public $tipo = 'nfe';
    public $cliente_id;
    public $data_emissao;
    public $data_saida;
    public $observacoes;
    public $itens = [];
    public $produto_id;
    public $quantidade = 1;
    public $valor_unitario = 0;
    
    public $clientes = [];
    public $produtos = [];
    
    public function mount()
    {
        $this->empresa = Empresa::first();
        $this->data_emissao = now()->format('Y-m-d\TH:i');
        $this->data_saida = now()->format('Y-m-d\TH:i');
        $this->clientes = Cliente::all();
        $this->produtos = Produto::all();
    }

    public function adicionarItem()
    {
        if (!$this->produto_id || $this->quantidade <= 0 || $this->valor_unitario <= 0) {
            session()->flash('error', 'Preencha todos os campos do item.');
            return;
        }

        $produto = Produto::find($this->produto_id);
        
        $this->itens[] = [
            'produto_id' => $produto->id,
            'codigo' => $produto->codigo,
            'nome' => $produto->nome,
            'quantidade' => $this->quantidade,
            'valor_unitario' => $this->valor_unitario,
            'valor_total' => $this->quantidade * $this->valor_unitario,
            'cfop' => $produto->cfop
        ];

        // Limpar campos
        $this->produto_id = null;
        $this->quantidade = 1;
        $this->valor_unitario = 0;
    }

    public function removerItem($index)
    {
        unset($this->itens[$index]);
        $this->itens = array_values($this->itens);
    }

    public function emitirNfe()
    {
        $this->validate([
            'cliente_id' => 'required',
            'data_emissao' => 'required',
            'itens' => 'required|array|min:1'
        ]);

        try {
            // Criar NFe
            $nfe = Nfe::create([
                'empresa_id' => $this->empresa->id,
                'cliente_id' => $this->cliente_id,
                'tipo' => $this->tipo,
                'serie' => $this->tipo === 'nfce' ? $this->empresa->serie_nfce : $this->empresa->serie_nfe,
                'numero' => $this->tipo === 'nfce' ? $this->empresa->proximoNumeroNfce() : $this->empresa->proximoNumeroNfe(),
                'data_emissao' => $this->data_emissao,
                'data_saida' => $this->data_saida,
                'valor_total' => collect($this->itens)->sum('valor_total'),
                'observacoes' => $this->observacoes,
                'status' => 'processando'
            ]);

            // Criar itens
            foreach ($this->itens as $index => $item) {
                NfeItem::create([
                    'nfe_id' => $nfe->id,
                    'produto_id' => $item['produto_id'],
                    'item' => $index + 1,
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'valor_total' => $item['valor_total'],
                    'cfop' => $item['cfop']
                ]);
            }

            // Processar NFe
            $nfeService = new NfeService($this->empresa);
            $xml = $nfeService->criarNfe($nfe);
            
            // Salvar XML
            $xmlPath = "nfes/{$nfe->tipo}_{$nfe->numero}.xml";
            Storage::put($xmlPath, $xml);
            
            // Enviar para SEFAZ
            $retorno = $nfeService->enviarNfe($xml);
            
            if ($retorno->cStat == 100) {
                // NFe autorizada
                $nfe->update([
                    'status' => 'autorizada',
                    'chave' => $retorno->protNFe->infProt->chNFe ?? null,
                    'protocolo' => $retorno->protNFe->infProt->nProt ?? null,
                    'xml_path' => $xmlPath
                ]);
                
                // Gerar PDF
                $pdf = $nfeService->gerarDanfe($xml);
                $pdfPath = "nfes/{$nfe->tipo}_{$nfe->numero}.pdf";
                Storage::put($pdfPath, $pdf);
                $nfe->update(['pdf_path' => $pdfPath]);
                
                session()->flash('success', 'NFe emitida com sucesso!');
                $this->reset(['cliente_id', 'observacoes', 'itens']);
                
            } else {
                // NFe rejeitada
                $nfe->update([
                    'status' => 'rejeitada',
                    'mensagem_retorno' => $retorno->xMotivo ?? 'Erro desconhecido'
                ]);
                
                session()->flash('error', 'NFe rejeitada: ' . ($retorno->xMotivo ?? 'Erro desconhecido'));
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao emitir NFe: ' . $e->getMessage());
        }
    }

    public function getValorTotalProperty()
    {
        return collect($this->itens)->sum('valor_total');
    }

    public function render()
    {
        return view('livewire.nfe-emissor');
    }
}