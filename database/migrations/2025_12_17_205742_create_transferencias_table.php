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
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->string('systemId');
            $table->enum('estado', ['aberto', 'fechado'])->default('fechado');
            $table->uuid('origem');
            $table->uuid('destino');
            $table->uuid('pessoa_id');
            $table->string('aprovado_por')->nullable();
            $table->integer('aprovado')->nullable(); //1-aprovado, 0-pendente, 2-reprovado
            $table->enum('regime', ['conveniencia', 'pedido', 'permuta'])->default('pedido');
            $table->string('nrProcesso');
            $table->string('permutador')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->string('abertoPor');

            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            $table->foreign('abertoPor')->references('id')->on('pessoas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};
