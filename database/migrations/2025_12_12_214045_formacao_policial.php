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
            $table->enum('academiaPolicial', ['acipol', 'matalane', 'macandzene']);
            $table->date('dataInicio')->nullable();
            $table->string('curso')->nullable();
            $table->date('dataFim')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
