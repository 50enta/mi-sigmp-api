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
        Schema::create('formacaoPolicial', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->enum('basico', ['matalane', 'macandzene'])->nullable();
            $table->date('dataConclusaoBasico')->nullable();
            $table->enum('medio', ['esapol'])->nullable();
            $table->date('dataConclusaoMedio')->nullable();
            $table->enum('superior', ['acipol'])->nullable();
            $table->date('dataConclusaoSuperior')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->uuid('pessoa_id');
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formacaoPolicial');
    }
};
