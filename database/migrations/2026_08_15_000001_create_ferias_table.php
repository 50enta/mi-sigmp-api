<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ferias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('systemId')->unique();
            $table->string('nrProcesso');
            $table->enum('estado', ['aberto', 'fechado'])->default('fechado');
            $table->uuid('pessoa_id');
            $table->date('dataInicio');
            $table->date('dataFim');
            $table->unsignedTinyInteger('diasFerias');
            $table->unsignedSmallInteger('saldoAntes');
            $table->unsignedSmallInteger('saldoDepois');
            $table->text('observacoes')->nullable();
            $table->uuid('abertoPor');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pessoa_id')->references('id')->on('pessoas')->cascadeOnDelete();
            $table->foreign('abertoPor')->references('id')->on('pessoas')->cascadeOnDelete();
            $table->index(['pessoa_id', 'dataInicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ferias');
    }
};
