<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('local_afectos', function (Blueprint $table) {
            $table->string('nrDespacho')->nullable()->after('despacho');
            $table->date('dataDespacho')->nullable()->after('nrDespacho');
        });
    }

    public function down(): void
    {
        Schema::table('local_afectos', function (Blueprint $table) {
            $table->dropColumn(['nrDespacho', 'dataDespacho']);
        });
    }
};
