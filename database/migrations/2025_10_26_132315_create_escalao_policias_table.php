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
        Schema::create('escalao_policias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('activo')->default(true);
            $table->uuid('escala_id');
            $table->uuid('pessoa_id');
            $table->string('despacho')->nullable();
            $table->date('dataInicio')->nullable();
            $table->date('dataFim')->nullable();
            $table->text('obs')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('escala_id')->references('id')->on('escalaos')->onDelete('cascade');
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalao_policias');
    }
};
