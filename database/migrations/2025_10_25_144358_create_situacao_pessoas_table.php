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
        Schema::create('situacao_pessoas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('situacao_id');
            $table->uuid('pessoa_id');
            $table->boolean('isMudanca')->default(false);
            $table->string('despacho')->nullable();
            $table->text('obs')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('situacao_id')->references('id')->on('situacaos')->onDelete('cascade');
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('situacao_pessoas');
    }
};
