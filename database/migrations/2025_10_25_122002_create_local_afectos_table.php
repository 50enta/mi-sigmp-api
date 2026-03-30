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
            $table->string('cargo')->nullable();
            $table->string('transferencia_id')->nullable();
            $table->string('reafetacao_id')->nullable();
            $table->string('despacho')->nullable();
            $table->boolean('isTransferencia')->default(false);
            $table->boolean('isReafetacao')->default(false);
            $table->text('observacoes')->nullable();
            $table->date('dataInicio');
            $table->date('dataFim')->nullable();

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
