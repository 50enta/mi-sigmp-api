<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formacaoPolicial', function (Blueprint $table) {
            $table->uuid('curso_id')->nullable()->after('curso');
            $table->foreign('curso_id')->references('id')->on('cursos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('formacaoPolicial', function (Blueprint $table) {
            $table->dropForeign(['curso_id']);
            $table->dropColumn('curso_id');
        });
    }
};
