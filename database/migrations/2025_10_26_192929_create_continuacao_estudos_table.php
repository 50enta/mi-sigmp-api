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
        Schema::create('continuacao_estudos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('despacho')->nullable();
            $table->string('instituicao')->nullable();
            $table->string('curso')->nullable();
            $table->string('nivelPretendido')->nullable();
            $table->enum('situacao', ['activo', 'inactivo'])->default('activo');
            $table->date('dataInicio')->nullable();
            $table->date('dataPrevisaoTermino')->nullable();
            $table->text('obs')->nullable();
            $table->uuid('pessoa_id');
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
        Schema::dropIfExists('continuacao_estudos');
    }
};
