<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pessoas', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('nip');
        });

        Schema::create('pessoa_telefones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pessoa_id');
            $table->string('numero', 50)->unique();
            $table->unsignedSmallInteger('ordem');
            $table->timestamps();

            $table->foreign('pessoa_id')->references('id')->on('pessoas')->cascadeOnDelete();
            $table->unique(['pessoa_id', 'ordem']);
        });

        Schema::dropIfExists('contactos');
    }

    public function down(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nip');
            $table->string('contactoPrincipal')->nullable();
            $table->string('contactoAlternativo')->nullable();
            $table->string('contactoEmergencia')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::dropIfExists('pessoa_telefones');

        Schema::table('pessoas', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });
    }
};
