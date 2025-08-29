<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nfe_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nfe_id')->constrained('nfes');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('acao'); // criada, autorizada, cancelada, etc.
            $table->json('dados_antes')->nullable();
            $table->json('dados_depois')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->index(['nfe_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfe_logs');
    }
};
