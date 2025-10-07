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
        Schema::create('locais', function (Blueprint $table) {
            $table->id();
            $table->text('local');
            $table->timestamps();
        });

        DB::table('locais')->insert(
            array(
                [
                    'local' =>  "Head Office",
                ],
                [
                    'local' => 'Terminal da Matola',
                ],
                [
                    'local' => 'Terminal da Beira',
                ],
                [
                    'local' => 'Terminal de Nacala',
                ]
            )
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locais');
    }
};
