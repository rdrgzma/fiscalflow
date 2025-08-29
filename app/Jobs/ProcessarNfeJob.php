<?php
// app/Jobs/ProcessarNfeJob.php

namespace App\Jobs;

use App\Models\Nfe;
use App\Services\NfeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessarNfeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $nfe;
    
    public $timeout = 300; // 5 minutos
    public $tries = 3;

    public function __construct(Nfe $nfe)
    {
        $this->nfe = $nfe;
    }

    public function handle()
    {
        try {
            Log::info("Iniciando processamento da NFe {$this->nfe->id}");
            
            $this->nfe->update(['status' => 'processando']);
            
            $nfeService = new NfeService($this->nfe->empresa);
            
            // Criar XML
            $xml = $nfeService->criarNfe($this->nfe);
            
            // Salvar XML temporário
            $xmlPath = "nfes/temp/{$this->nfe->tipo}_{$this->nfe->numero}_{time()}.xml";
            Storage::put($xmlPath, $xml);
            
            // Enviar para SEFAZ
            $retorno = $nfeService->enviarNfe($xml);
            
            if ($retorno->cStat == 100) {
                // NFe autorizada
                $xmlFinalPath = "nfes/{$this->nfe->tipo}_{$this->nfe->numero}.xml";
                Storage::move($xmlPath, $xmlFinalPath);
                
                $this->nfe->update([
                    'status' => 'autorizada',
                    'chave' => $retorno->protNFe->infProt->chNFe ?? null,
                    'protocolo' => $retorno->protNFe->infProt->nProt ?? null,
                    'xml_path' => $xmlFinalPath,
                    'mensagem_retorno' => 'NFe autorizada com sucesso'
                ]);
                
                // Gerar PDF em job separado
                GerarDanfeJob::dispatch($this->nfe);
                
                Log::info("NFe {$this->nfe->id} autorizada com sucesso");
                
            } else {
                // NFe rejeitada
                Storage::delete($xmlPath);
                
                $this->nfe->update([
                    'status' => 'rejeitada',
                    'mensagem_retorno' => $retorno->xMotivo ?? 'Erro desconhecido'
                ]);
                
                Log::error("NFe {$this->nfe->id} rejeitada: " . ($retorno->xMotivo ?? 'Erro desconhecido'));
            }
            
        } catch (\Exception $e) {
            $this->nfe->update([
                'status' => 'erro',
                'mensagem_retorno' => $e->getMessage()
            ]);
            
            Log::error("Erro ao processar NFe {$this->nfe->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        $this->nfe->update([
            'status' => 'erro',
            'mensagem_retorno' => 'Falha após múltiplas tentativas: ' . $exception->getMessage()
        ]);
        
        Log::error("Job falhou definitivamente para NFe {$this->nfe->id}: " . $exception->getMessage());
    }
}
