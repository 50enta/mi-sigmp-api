<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assentos_biograficos', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['aberto', 'fechado'])->default('fechado');
            $table->string('systemId')->unique();
            $table->string('nrProcesso');
            $table->uuid('pessoa_id');
            $table->string('tipoRegisto');
            $table->date('dataRegisto');
            $table->text('descricao');
            $table->string('documento')->nullable();
            $table->uuid('abertoPor');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pessoa_id')->references('id')->on('pessoas')->cascadeOnDelete();
            $table->foreign('abertoPor')->references('id')->on('pessoas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assentos_biograficos');
    }
};
