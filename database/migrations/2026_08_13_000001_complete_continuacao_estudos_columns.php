<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('continuacao_estudos', function (Blueprint $table) {
            $table->string('despacho')->nullable()->change();

            if (! Schema::hasColumn('continuacao_estudos', 'systemId')) {
                $table->string('systemId')->nullable()->after('id');
            }

            if (! Schema::hasColumn('continuacao_estudos', 'nrDespacho')) {
                $table->string('nrDespacho')->nullable()->after('nrProcesso');
            }

            if (! Schema::hasColumn('continuacao_estudos', 'dataDespacho')) {
                $table->date('dataDespacho')->nullable()->after('nrDespacho');
            }
        });
    }

    public function down(): void
    {
        // Intentionally kept because some existing installations already had
        // these columns before they were formalized in a migration.
    }
};
