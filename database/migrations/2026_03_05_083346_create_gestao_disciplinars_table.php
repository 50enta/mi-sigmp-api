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
        Schema::create('gestao_disciplinars', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['aberto', 'fechado'])->default('aberto');
            $table->string('nrProcesso');
            $table->string('systemId');
            $table->uuid('pessoa_id');
            $table->string('despacho')->nullable();
            $table->date('dataDecisao')->nullable();
            $table->text('proposta');
            $table->text('infraccao');
            $table->string('origem');
            $table->string('abertoPor');
            $table->string('decididoPor')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            $table->foreign('decididoPor')->references('id')->on('pessoas')->onDelete('cascade');
            $table->foreign('abertoPor')->references('id')->on('pessoas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gestao_disciplinars');
    }
};
