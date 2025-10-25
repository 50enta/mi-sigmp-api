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
        Schema::create('locals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('activo')->default(true);
            $table->boolean('isLogico')->default(false);
            $table->integer('nivel')->default(false);
            $table->string('descricao');
            $table->text('comentarios')->nullable();
            $table->boolean('hasPai')->default(false);
            $table->uuid('pai_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pai_id')->references('id')->on('locals')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locals');
    }
};
