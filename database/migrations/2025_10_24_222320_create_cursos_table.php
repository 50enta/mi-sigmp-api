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
        Schema::create('cursos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('descricao');
            $table->date('dataInicio')->nullable();
            $table->string('especialidade')->nullable();
            $table->date('dataFim')->nullable();
            $table->string('numero')->nullable();
            $table->string('grau')->nullable();
            $table->string('local')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
