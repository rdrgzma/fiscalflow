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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('ncm', 8);
            $table->string('cfop', 4);
            $table->string('unidade', 6);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('estoque', 10, 3)->default(0);
            $table->string('cest', 10)->nullable();
            $table->decimal('peso_bruto', 10, 3)->default(0);
            $table->decimal('peso_liquido', 10, 3)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
