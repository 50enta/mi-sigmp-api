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
        Schema::create('incidentes', function (Blueprint $table) {
            $table->id();
            $table->integer('reportado_por')->notNullable();
            $table->integer('registado_por')->notNullable();
            $table->enum('estado', [1, 2, 3])->default(1);
            $table->text('descricao')->notNullable();
            $table->text('circunstancias')->nullable();
            $table->timestamp('dataAcontecimento')->nullable();
            $table->char('classe', 5)->notNullable();
            $table->boolean('com_arquivada')->default(false);
            $table->boolean('com_arquivada_cta')->default(false);
            $table->timestamp('com_arquivada_data')->nullable();
            $table->timestamp('com_arquivada_cta_data')->nullable();
            $table->text('medidas_imediatas')->notNullable();
            $table->text('causas_preliminares')->nullable();
            $table->integer('terceiros_feridos')->default(0);
            $table->integer('contratados_feridos')->default(0);
            $table->integer('samcol_feridos')->default(0);
            $table->integer('terceiros_fatalidades')->default(0);
            $table->integer('contratados_fatalidades')->default(0);
            $table->integer('samcol_fatalidades')->default(0);
            $table->text('dm_samcol')->nullable();
            $table->text('dm_terceiros')->nullable();
            $table->text('dm_contratados')->nullable();
            $table->text('di_outros')->nullable();
            $table->text('di_imprensa')->nullable();
            $table->text('di_samcol')->nullable();
            $table->json('comunicacao')->nullable();
            $table->json('regrasDeOuro')->nullable();
            $table->text('planoDeAccao')->nullable();
            $table->integer('local')->notNullable();
            $table->integer('local_reporter')->notNullable();
            $table->timestamp('data_aprovacao')->nullable();
            $table->integer('quem_fechou')->nullable();
            $table->integer('severidade')->notNullable();
            $table->integer('tipo_incidente')->notNullable();
            $table->text('obs')->nullable();
            $table->text('autores')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};
