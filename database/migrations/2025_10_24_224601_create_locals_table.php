<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            $table->integer('nivel')->default(2);
            $table->string('nome');
            $table->string('code')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('locals')
                ->onDelete('set null');
        });

        // === Inserir dados iniciais (seeding dentro da migration) ===
        $locals = [
            // Nível 1 - Unidades principais
            [
                'id' => Str::uuid(),
                'nome' => 'República de Moçambique',
                'nivel' => 1,
                'parent_id' => null,
                'activo' => true,
                'code'=>'MZ',
                'description'=>'República de Moçambique'
            ],
        ];

        foreach ($locals as $local) {
            DB::table('locals')->insert(array_merge($local, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locals');
    }
};
