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
        Schema::create('promocaos', function (Blueprint $table) {
            $table->enum('estado', ['aberto', 'fechado'])->default('fechado');
            $table->string('systemId');
            $table->string('nrProcesso');
            $table->uuid('pessoa_id');
            $table->string('abertoPor');
            $table->string('categoriaActual');
            $table->string('novaCategoria');
            $table->string('obs')->nullable();
            $table->string('despacho')->nullable();
            $table->string('nrDespacho');
            $table->date('dataDespacho');
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
        Schema::dropIfExists('promocaos');
    }
};
