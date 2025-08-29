<?php
// app/Console/Commands/VerificarSistemaCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Services\NfeConsultaService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VerificarSistemaCommand extends Command
{
    protected $signature = 'nfe:verificar-sistema';
    protected $description = 'Verifica a saúde do sistema NFe';

    public function handle()
    {
        $this->info('🔍 Verificando sistema NFe...');
        
        $problemas = 0;
        
        // Verificar conexão com banco
        try {
            DB::connection()->getPdo();
            $this->line('✅ Conexão com banco de dados: OK');
        } catch (\Exception $e) {
            $this->error('❌ Conexão com banco de dados: FALHA');
            $problemas++;
        }
        
        // Verificar diretórios
        $diretorios = ['storage/app/nfes', 'storage/app/certificados', 'storage/app/backups'];
        foreach ($diretorios as $dir) {
            if (is_writable($dir)) {
                $this->line("✅ Diretório {$dir}: OK");
            } else {
                $this->error("❌ Diretório {$dir}: Não gravável");
                $problemas++;
            }
        }
        
        // Verificar certificados
        $empresas = Empresa::all();
        foreach ($empresas as $empresa) {
            if (Storage::exists($empresa->certificado_path)) {
                $this->line("✅ Certificado empresa {$empresa->razao_social}: OK");
            } else {
                $this->error("❌ Certificado empresa {$empresa->razao_social}: Não encontrado");
                $problemas++;
            }
        }
        
        // Verificar conexão com SEFAZ
        if ($empresas->count() > 0) {
            try {
                $consultaService = new NfeConsultaService($empresas->first());
                $status = $consultaService->consultarStatusServico();
                
                if ($status['success'] && $status['status'] === 'online') {
                    $this->line('✅ Conexão com SEFAZ: OK');
                } else {
                    $this->error('❌ Conexão com SEFAZ: Indisponível');
                    $problemas++;
                }
            } catch (\Exception $e) {
                $this->error('❌ Conexão com SEFAZ: Erro - ' . $e->getMessage());
                $problemas++;
            }
        }
        
        // Verificar filas
        try {
            $jobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            
            $this->line("✅ Jobs na fila: {$jobs}");
            if ($failedJobs > 0) {
                $this->warn("⚠️  Jobs falhados: {$failedJobs}");
            } else {
                $this->line('✅ Nenhum job falhado');
            }
        } catch (\Exception $e) {
            $this->error('❌ Erro ao verificar filas: ' . $e->getMessage());
            $problemas++;
        }
        
        // Resultado final
        if ($problemas === 0) {
            $this->info('🎉 Sistema funcionando perfeitamente!');
        } else {
            $this->error("❌ Encontrados {$problemas} problema(s). Verifique os logs.");
        }
        
        return $problemas === 0 ? 0 : 1;
    }
}
