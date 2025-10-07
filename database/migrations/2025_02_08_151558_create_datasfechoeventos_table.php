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
        Schema::create('datasfechoeventos', function (Blueprint $table) {
            $table->id();
            $table->timestamp('data_fecho');
            $table->boolean('activo')->default(true);	
            $table->foreignId('user_id')->constrained();
            $table->foreignId('incidente')->nullable();
            $table->foreignId('near_miss')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasfechoeventos');
    }
};
