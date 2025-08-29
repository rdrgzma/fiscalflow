<?php
// app/Livewire/NfeConsulta.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Nfe;
use App\Services\NfeConsultaService;

class NfeConsulta extends Component
{
    public $nfe;
    public $consultaResultado = null;
    public $consultandoStatus = false;

    public function mount(Nfe $nfe)
    {
        $this->nfe = $nfe;
    }

    public function consultarStatus()
    {
        $this->consultandoStatus = true;
        $this->consultaResultado = null;

        try {
            $consultaService = new NfeConsultaService($this->nfe->empresa);
            $resultado = $consultaService->consultarNfe($this->nfe);

            $this->consultaResultado = $resultado;
            
            if ($resultado['success']) {
                $this->nfe->refresh(); // Recarregar dados atualizados
                session()->flash('success', 'Status consultado com sucesso!');
            } else {
                session()->flash('error', 'Erro na consulta: ' . $resultado['error']);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao consultar status: ' . $e->getMessage());
        } finally {
            $this->consultandoStatus = false;
        }
    }

    public function render()
    {
        return view('livewire.nfe-consulta');
    }
}
