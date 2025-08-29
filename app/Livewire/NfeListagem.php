<?php
// app/Livewire/NfeListagem.php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Nfe;
use Illuminate\Support\Facades\Storage;

class NfeListagem extends Component
{
    use WithPagination;
    
    public $search = '';
    public $statusFilter = '';
    public $tipoFilter = '';
    
    protected $queryString = ['search', 'statusFilter', 'tipoFilter'];

    public function downloadXml($nfeId)
    {
        $nfe = Nfe::find($nfeId);
        
        if ($nfe && $nfe->xml_path && Storage::exists($nfe->xml_path)) {
            return Storage::download($nfe->xml_path, "NFe_{$nfe->numero}.xml");
        }
        
        session()->flash('error', 'Arquivo XML não encontrado.');
    }
    
    public function downloadPdf($nfeId)
    {
        $nfe = Nfe::find($nfeId);
        
        if ($nfe && $nfe->pdf_path && Storage::exists($nfe->pdf_path)) {
            return Storage::download($nfe->pdf_path, "NFe_{$nfe->numero}.pdf");
        }
        
        session()->flash('error', 'Arquivo PDF não encontrado.');
    }

    public function render()
    {
        $nfes = Nfe::with(['cliente', 'empresa'])
            ->when($this->search, function ($query) {
                $query->where('numero', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cliente', function ($q) {
                          $q->where('nome', 'like', '%' . $this->search . '%');
                      });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->tipoFilter, function ($query) {
                $query->where('tipo', $this->tipoFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.nfe-listagem', compact('nfes'));
    }
}