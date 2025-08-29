<?php
// app/Livewire/Dashboard.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Nfe;
use App\Models\Cliente;
use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $periodo = '30'; // dias
    public $dadosEstatisticas = [];
    public $graficoVendas = [];
    public $topClientes = [];
    public $topProdutos = [];

    public function mount()
    {
        $this->carregarDados();
    }

    public function updatedPeriodo()
    {
        $this->carregarDados();
    }

    public function carregarDados()
    {
        $dataInicio = Carbon::now()->subDays($this->periodo);
        
        // Estatísticas gerais
        $this->dadosEstatisticas = [
            'total_nfes' => Nfe::where('created_at', '>=', $dataInicio)->count(),
            'nfes_autorizadas' => Nfe::where('created_at', '>=', $dataInicio)
                                     ->where('status', 'autorizada')->count(),
            'valor_total_vendas' => Nfe::where('created_at', '>=', $dataInicio)
                                       ->where('status', 'autorizada')
                                       ->sum('valor_total'),
            'ticket_medio' => Nfe::where('created_at', '>=', $dataInicio)
                                 ->where('status', 'autorizada')
                                 ->avg('valor_total') ?? 0,
            'total_clientes' => Cliente::count(),
            'total_produtos' => Produto::count()
        ];

        // Gráfico de vendas por dia
        $this->graficoVendas = Nfe::select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('COUNT(*) as quantidade'),
                DB::raw('SUM(valor_total) as valor')
            )
            ->where('created_at', '>=', $dataInicio)
            ->where('status', 'autorizada')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(function ($item) {
                return [
                    'data' => Carbon::parse($item->data)->format('d/m'),
                    'quantidade' => $item->quantidade,
                    'valor' => $item->valor
                ];
            })
            ->toArray();

        // Top 5 clientes
        $this->topClientes = Nfe::select('cliente_id')
            ->selectRaw('SUM(valor_total) as total_compras')
            ->selectRaw('COUNT(*) as total_nfes')
            ->with('cliente:id,nome')
            ->where('created_at', '>=', $dataInicio)
            ->where('status', 'autorizada')
            ->groupBy('cliente_id')
            ->orderBy('total_compras', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        // Top 5 produtos mais vendidos
        $this->topProdutos = DB::table('nfe_itens')
            ->select('produto_id')
            ->selectRaw('SUM(quantidade) as total_vendido')
            ->selectRaw('SUM(valor_total) as receita_total')
            ->join('nfes', 'nfes.id', '=', 'nfe_itens.nfe_id')
            ->join('produtos', 'produtos.id', '=', 'nfe_itens.produto_id')
            ->where('nfes.created_at', '>=', $dataInicio)
            ->where('nfes.status', 'autorizada')
            ->groupBy('produto_id')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $produto = Produto::find($item->produto_id);
                return [
                    'nome' => $produto->nome,
                    'codigo' => $produto->codigo,
                    'total_vendido' => $item->total_vendido,
                    'receita_total' => $item->receita_total
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
