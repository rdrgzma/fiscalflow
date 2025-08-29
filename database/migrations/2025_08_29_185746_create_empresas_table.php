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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
             $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj', 14)->unique();
            $table->string('ie', 20);
            $table->string('im', 20)->nullable();
            $table->string('cep', 8);
            $table->string('logradouro');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('uf', 2);
            $table->integer('cod_municipio');
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->enum('crt', [1, 2, 3]); // 1=Simples Nacional, 2=Simples Excesso, 3=Regime Normal
            $table->string('certificado_path');
            $table->string('certificado_senha');
            $table->integer('serie_nfe')->default(1);
            $table->integer('serie_nfce')->default(1);
            $table->integer('numero_nfe')->default(1);
            $table->integer('numero_nfce')->default(1);
            $table->enum('ambiente', [1, 2])->default(2); // 1=Produção, 2=Homologação
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
