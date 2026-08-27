<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promocaos', function (Blueprint $table) {
            $table->string('modalidade')->nullable()->after('novaCategoria');
        });
    }

    public function down(): void
    {
        Schema::table('promocaos', function (Blueprint $table) {
            $table->dropColumn('modalidade');
        });
    }
};
