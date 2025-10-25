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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pessoa_id');
            $table->text('info')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
