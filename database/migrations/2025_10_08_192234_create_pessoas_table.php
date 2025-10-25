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
            $table->date('dataNasc')->nullable();
            $table->string('nuit')->nullable();
            $table->string('estadoCivil')->nullable();
            $table->string('sexo')->nullable();
            $table->string('bi')->nullable();
            $table->string('distrito')->nullable();
            $table->string('provincia')->nullable();
            $table->string('residencia')->nullable();
            $table->string('grupoSangue')->nullable();
            $table->string('nrProcesso')->nullable();
            $table->string('situacaoDisciplinar')->nullable();
            $table->string('situacao')->nullable();
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
