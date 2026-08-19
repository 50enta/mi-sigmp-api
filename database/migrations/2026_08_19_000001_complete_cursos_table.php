<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->enum('categoria', ['basico', 'medio', 'superior'])->nullable()->after('grau');
            $table->string('numero_despacho')->nullable()->after('numero');
            $table->string('documento_despacho')->nullable()->after('numero_despacho');
            $table->unsignedInteger('total_esperado')->nullable()->after('local');
            $table->boolean('cancelado')->default(false)->after('total_esperado');
            $table->index(['categoria', 'cancelado']);
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropIndex(['categoria', 'cancelado']);
            $table->dropColumn([
                'categoria',
                'numero_despacho',
                'documento_despacho',
                'total_esperado',
                'cancelado',
            ]);
        });
    }
};
