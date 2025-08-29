<?php
// app/Livewire/NfeCancelamento.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Nfe;
use App\Services\NfeCancelamentoService;

class NfeCancelamento extends Component
{
    public $nfe;
    public $justificativa = '';
    public $showModal = false;

    protected $rules = [
        'justificativa' => 'required|min:15|max:255'
    ];

    protected $messages = [
        'justificativa.required' => 'A justificativa é obrigatória',
        'justificativa.min' => 'A justificativa deve ter no mínimo 15 caracteres',
        'justificativa.max' => 'A justificativa deve ter no máximo 255 caracteres'
    ];

    public function mount(Nfe $nfe)
    {
        $this->nfe = $nfe;
    }

    public function cancelar()
    {
        $this->validate();

        try {
            $cancelamentoService = new NfeCancelamentoService($this->nfe->empresa);
            $resultado = $cancelamentoService->cancelarNfe($this->nfe, $this->justificativa);

            if ($resultado['success']) {
                session()->flash('success', $resultado['message']);
                $this->showModal = false;
                $this->dispatch('nfe-cancelada');
            } else {
                session()->flash('error', $resultado['message']);
            }

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.nfe-cancelamento');
    }
}
