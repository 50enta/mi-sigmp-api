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
        Schema::create('formacoesComplementares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cursoComplementar');
            $table->string('instituicao');
            $table->string('anoConlusao');
            $table->string('certificado')->nullable();
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
        Schema::dropIfExists('formacoesComplementares');
    }
};
