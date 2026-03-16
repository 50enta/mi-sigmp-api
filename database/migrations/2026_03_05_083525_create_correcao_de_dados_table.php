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
        Schema::create('correcao_de_dados', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['aberto', 'fechado'])->default('fechado');
            $table->string('nrProcesso');
            $table->uuid('pessoa_id');
            $table->string('comprovativo');
            $table->integer('tipoCorrecao');
            $table->string('novoNome')->nullable();
            $table->string('motivoEobs');
            $table->string('dataNasc')->nullable();
            $table->string('abertoPor');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            $table->foreign('abertoPor')->references('id')->on('pessoas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correcao_de_dados');
    }
};
