<?php
// app/Services/NfeAuditoriaService.php

namespace App\Services;

use App\Models\NfeLog;
use App\Models\Nfe;

class NfeAuditoriaService
{
    public static function registrarLog(Nfe $nfe, $acao, $dadosAntes = null, $dadosDespois = null, $observacoes = null)
    {
        NfeLog::create([
            'nfe_id' => $nfe->id,
            'usuario_id' => auth()->id(),
            'acao' => $acao,
            'dados_antes' => $dadosAntes,
            'dados_depois' => $dadosDespois,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'observacoes' => $observacoes
        ]);
    }

    public static function obterHistorico(Nfe $nfe)
    {
        return NfeLog::where('nfe_id', $nfe->id)
                    ->with('usuario')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
}