<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ferias', function (Blueprint $table) {
            $table->string('nrDespacho')->nullable()->after('dataFim');
            $table->date('dataDespacho')->nullable()->after('nrDespacho');
            $table->string('despacho')->nullable()->after('dataDespacho');
        });
    }

    public function down(): void
    {
        Schema::table('ferias', function (Blueprint $table) {
            $table->dropColumn(['nrDespacho', 'dataDespacho', 'despacho']);
        });
    }
};
