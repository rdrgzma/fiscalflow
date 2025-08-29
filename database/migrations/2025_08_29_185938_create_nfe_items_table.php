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
        Schema::create('nfe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nfe_id')->constrained('nfes')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->integer('item'); // Número sequencial do item
            $table->decimal('quantidade', 10, 3);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('valor_total', 12, 2);
            $table->string('cfop', 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfe_items');
    }
};
