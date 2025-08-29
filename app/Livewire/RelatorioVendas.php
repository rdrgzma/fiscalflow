<?php
// app/Livewire/RelatorioVendas.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Nfe;
use App\Models\Cliente;
use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RelatorioVendas extends Component
{
    public $dataInicio;
    public $dataFim;
    public $cliente_id = '';
    public $status = '';
    public $tipo = '';
    
    public $relatorio = [];
    public $totalGeral = 0;
    public $quantidadeTotal = 0;

    public function mount()
    {
        $this->dataInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dataFim = Carbon::now()->format('Y-m-d');
    }

    public function gerarRelatorio()
    {
        $query = Nfe::with(['cliente', 'itens.produto'])
            ->whereBetween('data_emissao', [$this->dataInicio, $this->dataFim]);

        if ($this->cliente_id) {
            $query->where('cliente_id', $this->cliente_id);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        $nfes = $query->orderBy('data_emissao', 'desc')->get();

        $this->relatorio = $nfes->map(function ($nfe) {
            return [
                'numero' => $nfe->numeroFormatado,
                'tipo' => strtoupper($nfe->tipo),
                'cliente' => $nfe->cliente->nome,
                'data_emissao' => $nfe->data_emissao->format('d/m/Y'),
                'status' => $nfe->status,
                'valor_total' => $nfe->valor_total,
                'itens' => $nfe->itens->map(function ($item) {
                    return [
                        'produto' => $item->produto->nome,
                        'quantidade' => $item->quantidade,
                        'valor_unitario' => $item->valor_unitario,
                        'valor_total' => $item->valor_total
                    ];
                })
            ];
        })->toArray();

        $this->totalGeral = $nfes->where('status', 'autorizada')->sum('valor_total');
        $this->quantidadeTotal = $nfes->where('status', 'autorizada')->count();
    }

    public function exportarPdf()
    {
        // Implementar exportação PDF
        $this->dispatch('exportar-pdf', ['relatorio' => $this->relatorio]);
    }

    public function exportarExcel()
    {
        // Implementar exportação Excel
        $this->dispatch('exportar-excel', ['relatorio' => $this->relatorio]);
    }

    public function render()
    {
        $clientes = Cliente::orderBy('nome')->get();
        
        return view('livewire.relatorio-vendas', compact('clientes'));
    }
}