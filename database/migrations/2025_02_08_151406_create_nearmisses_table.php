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
        Schema::create('nearmisses', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', [1, 2, 3])->default(1);
            $table->text('descricao')->notNullable();
            $table->text('detalhes')->nullable();
            $table->boolean('stop_card')->default(false);
            $table->integer('reportado_por')->notNullable();
            $table->integer('local_reporter')->notNullable();
            $table->integer('registado_por')->notNullable();
            $table->timestamp('dataAcontecimento')->notNullable();
            $table->char('classe', 1)->notNullable();
            $table->text('medidas_imediatas')->notNullable();
            $table->text('planoDeAccao')->nullable();
            $table->timestamp('data_aprovacao')->nullable();
            $table->json('responsaveis')->notNullable();
            $table->text('quem_fechou')->nullable();
            $table->text('obs')->nullable();
            $table->integer('local')->notNullable();
            $table->json('regrasDeOuro')->nullable();
            $table->json('possiveisDanos')->nullable();
            $table->boolean('boapratica')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nearmisses');
    }
};
