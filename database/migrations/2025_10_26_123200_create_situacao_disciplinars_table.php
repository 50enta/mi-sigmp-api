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
        Schema::create('situacao_disciplinars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('activo')->default(true);
            $table->string('nrProcesso')->unique();
            $table->uuid('pessoa_id');
            $table->string('local')->nullable();
            $table->text('proposta')->nullable();
            $table->string('abertoPor')->nullable();
            $table->date('dataAbertura')->nullable();
            $table->string('fechadoPor')->nullable();
            $table->date('dataFecho')->nullable();
            $table->text('obsFecho')->nullable();
            $table->integer('aprovado')->default(0);
            $table->string('aprovador')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('situacao_disciplinars');
    }
};
