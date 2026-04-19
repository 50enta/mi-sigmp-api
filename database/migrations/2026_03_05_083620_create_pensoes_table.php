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
        Schema::create('pensoes', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['aberto', 'fechado'])->default('fechado');
            $table->string('systemId');
            $table->uuid('pessoa_id');
            $table->string('motivo');
            $table->string('despacho')->nullable();
            $table->string('nrDespacho');
            $table->date('dataDespacho');
            $table->string('abertoPor');
            $table->string('nrProcesso');
            $table->enum('estadoActual', [
                'Reserva',
                'Aposentado',
                'Activo',
            ]);
            $table->enum('novoEstado', [
                'Reserva',
                'Aposentado',
                'Activo',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pensoes');
    }
};
