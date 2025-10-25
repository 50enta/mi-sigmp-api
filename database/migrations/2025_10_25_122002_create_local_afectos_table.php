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
        Schema::create('local_afectos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('local_id');
            $table->uuid('pessoa_id');
            $table->string('despacho')->nullable();
            $table->date('dataInicio')->nullable();
            $table->date('dataFim')->nullable();
            $table->boolean('isTransferencia')->default(false);
            $table->uuid('transferidor_id')->nullable();
            $table->string('aprovador')->nullable();
            $table->integer('aprovado')->nullable(); //1-aprovado, 0-pendente, 2-reprovado
            $table->string('local_origem')->nullable();
            $table->enum('regime', ['Pedido', 'Permuta'])->nullable();
            $table->string('permutador')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('local_id')->references('id')->on('locals')->onDelete('cascade');
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_afectos');
    }
};
