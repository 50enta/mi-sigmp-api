<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categoria_policias', function (Blueprint $table) {
            $table->date('dataDespacho')->nullable()->after('nrDespacho');
        });

        Schema::table('especialidade_pessoas', function (Blueprint $table) {
            $table->date('dataDespacho')->nullable()->after('nrDespacho');
        });

        Schema::table('cursos', function (Blueprint $table) {
            $table->date('data_despacho')->nullable()->after('numero_despacho');
        });
    }

    public function down(): void
    {
        Schema::table('categoria_policias', function (Blueprint $table) {
            $table->dropColumn('dataDespacho');
        });

        Schema::table('especialidade_pessoas', function (Blueprint $table) {
            $table->dropColumn('dataDespacho');
        });

        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn('data_despacho');
        });
    }
};
