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
        Schema::create('situacao_pessoas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('situacao', [
                'SUSPENSO',
                'EXONERADO',
                'EXPULSO',
                'MORTO',
                'RESERVADO',
                'APOSENTADO'
            ]);
            $table->uuid('pessoa_id');
            $table->string('despacho')->nullable();
            $table->text('obs')->nullable();
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('situacao_pessoas');
    }
};
