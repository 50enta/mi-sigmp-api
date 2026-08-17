<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialidade_pessoas', function (Blueprint $table) {
            $table->string('nrDespacho')->nullable()->after('nrProcesso');
        });
    }

    public function down(): void
    {
        Schema::table('especialidade_pessoas', function (Blueprint $table) {
            $table->dropColumn('nrDespacho');
        });
    }
};
