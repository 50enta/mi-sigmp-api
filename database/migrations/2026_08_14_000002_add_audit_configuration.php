<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->boolean('log_reads')->default(false);
            $table->boolean('log_creates')->default(true);
            $table->boolean('log_updates')->default(true);
            $table->boolean('log_deletes')->default(true);
            $table->boolean('capture_ip')->default(true);
            $table->boolean('capture_user_agent')->default(true);
            $table->unsignedSmallInteger('retention_days')->default(365);
            $table->timestamps();
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->string('event', 30)->nullable()->index();
            $table->string('method', 10)->nullable();
            $table->string('path')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('metadata')->nullable();
        });

        DB::table('audit_settings')->insert([
            'enabled' => true,
            'log_reads' => false,
            'log_creates' => true,
            'log_updates' => true,
            'log_deletes' => true,
            'capture_ip' => true,
            'capture_user_agent' => true,
            'retention_days' => 365,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('audit_trails', function (Blueprint $table) {
            $table->dropColumn(['event', 'method', 'path', 'ip_address', 'user_agent', 'http_status', 'metadata']);
        });
        Schema::dropIfExists('audit_settings');
    }
};
