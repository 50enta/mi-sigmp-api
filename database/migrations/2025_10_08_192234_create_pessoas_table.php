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
        Schema::create('pessoas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('aprovado')->default(0);
            $table->string('nip')->unique();
            $table->boolean('isGerivel')->default(false);
            $table->string('nomeCompleto');
            $table->string('nomeMae')->nullable();
            $table->string('nomePai')->nullable();
            $table->date('dataNasc')->nullable();
            $table->string('nuit')->nullable();
            $table->enum('estadoCivil', ['Solteiro', 'Casado', 'Divorciado', 'Viuvo'])->nullable();
            $table->enum('grupoSangue', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->string('distrito')->nullable();
            $table->enum('provincia', ['Maputo Cidade', 'Maputo Provincia', 'Gaza', 'Inhambane', 'Sofala', 'Manica', 'Zambezia', 'Nampula', 'Tete', 'Cabo Delgado', 'Niassa'])->nullable();
            $table->string('residencia')->nullable();
            $table->enum('genero', ['Masculino', 'Feminino', 'Outro'])->nullable();
            $table->string('BI')->nullable();
            $table->decimal('altura', 5, 2)->nullable();
            $table->text('linguas')->nullable();
            $table->integer('stepFinished')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
