<?php
// app/Jobs/GerarDanfeJob.php

namespace App\Jobs;

use App\Models\Nfe;
use App\Services\NfeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GerarDanfeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $nfe;

    public function __construct(Nfe $nfe)
    {
        $this->nfe = $nfe;
    }

    public function handle()
    {
        if (!$this->nfe->xml_path || !Storage::exists($this->nfe->xml_path)) {
            throw new \Exception("XML não encontrado para gerar DANFE");
        }

        $xml = Storage::get($this->nfe->xml_path);
        $nfeService = new NfeService($this->nfe->empresa);
        
        $logoPath = storage_path('app/public/logo.png');
        $pdf = $nfeService->gerarDanfe($xml, file_exists($logoPath) ? $logoPath : null);
        
        $pdfPath = "nfes/{$this->nfe->tipo}_{$this->nfe->numero}.pdf";
        Storage::put($pdfPath, $pdf);
        
        $this->nfe->update(['pdf_path' => $pdfPath]);
    }
}