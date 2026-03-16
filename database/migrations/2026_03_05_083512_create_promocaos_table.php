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
            $table->string('nrProcesso');
            $table->uuid('pessoa_id');
            $table->string('despacho');
            $table->string('abertoPor');
            $table->string('modalidade')->nullable();
            $table->date('data');
            $table->string('proposta');
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
