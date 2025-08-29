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
        Schema::create('nfes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->enum('tipo', ['nfe', 'nfce']);
            $table->integer('serie');
            $table->integer('numero');
            $table->string('chave', 44)->nullable();
            $table->enum('status', ['rascunho', 'processando', 'autorizada', 'rejeitada', 'cancelada'])->default('rascunho');
            $table->datetime('data_emissao');
            $table->datetime('data_saida')->nullable();
            $table->decimal('valor_total', 12, 2);
            $table->text('observacoes')->nullable();
            $table->json('dados_xml')->nullable(); // Para armazenar o XML completo
            $table->text('protocolo')->nullable();
            $table->text('mensagem_retorno')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfes');
    }
};
