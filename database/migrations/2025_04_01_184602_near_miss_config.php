<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('closeConfig', function (Blueprint $table) {
            $table->id();
            $table->integer('classeA')->notNullable();
            $table->integer('classeB')->notNullable();
            $table->integer('classeC')->notNullable();
        });

        DB::table('closeConfig')->insert([
            [
                'classeA' => '7',
                'classeB' => '10',
                'classeC' => '15'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closeConfig');
    }
};
