<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contactos', function (Blueprint $table) {
            $table->uuid('pessoa_id')->nullable()->after('id');
            $table->string('email')->nullable()->after('nip');
            $table->json('telefones')->nullable()->after('email');
        });

        $pessoas = DB::table('pessoas')->pluck('id', 'nip');

        DB::table('contactos')->orderBy('id')->each(function (object $contacto) use ($pessoas) {
            $telefones = array_values(array_filter([
                $contacto->contactoPrincipal,
                $contacto->contactoAlternativo,
                $contacto->contactoEmergencia,
            ]));

            DB::table('contactos')->where('id', $contacto->id)->update([
                'pessoa_id' => $pessoas->get($contacto->nip),
                'telefones' => json_encode($telefones),
            ]);
        });

        Schema::table('contactos', function (Blueprint $table) {
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->nullOnDelete();
            $table->unique('pessoa_id');
        });
    }

    public function down(): void
    {
        Schema::table('contactos', function (Blueprint $table) {
            $table->dropUnique(['pessoa_id']);
            $table->dropForeign(['pessoa_id']);
            $table->dropColumn(['pessoa_id', 'email', 'telefones']);
        });
    }
};
