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
        Schema::create('continuacao_estudos', function (Blueprint $table) {
            $table->id();
            $table->string('nrProcesso');
            $table->enum('estado', ['aberto', 'fechado'])->default('fechado');
            $table->string('despacho')->nullable();
            $table->string('instituicao');
            $table->string('curso');
            $table->string('nivelPretendido');
            $table->uuid('pessoa_id');
            $table->string('abertoPor');
            $table->date('data');
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
        Schema::dropIfExists('continuacao_estudos');
    }
};
