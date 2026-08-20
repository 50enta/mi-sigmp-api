<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_settings', function (Blueprint $table) {
            $table->boolean('log_logins')->default(true)->after('log_deletes');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->uuid('pessoa_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('audit_settings', function (Blueprint $table) {
            $table->dropColumn('log_logins');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->uuid('pessoa_id')->nullable(false)->change();
        });
    }
};
