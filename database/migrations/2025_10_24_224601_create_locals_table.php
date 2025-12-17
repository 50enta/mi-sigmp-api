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
            $table->integer('nivel')->default(1);
            $table->string('nome');
            $table->uuid('pai_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pai_id')
                ->references('id')
                ->on('locals')
                ->onDelete('set null');
        });

        // === Inserir dados iniciais (seeding dentro da migration) ===
        $locals = [
            // Nível 1 - Unidades principais
            [
                'id' => Str::uuid(),
                'nome' => 'Posto Policial George Dimitrov',
                'nivel' => 1,
                'pai_id' => null,
                'activo' => true,
            ],
            [
                'id' => Str::uuid(),
                'nome' => '15a Esquadra',
                'nivel' => 1,
                'pai_id' => null,
                'activo' => true,
            ],

            // Nível 2 - Subunidades (filhos)
            [
                'id' => Str::uuid(),
                'nome' => '6a Esquadra',
                'nivel' => 1,
                'pai_id' => null, // será atualizado abaixo
                'activo' => true,
            ],
            [
                'id' => Str::uuid(),
                'nome' => 'Posto Policia de Volante 6',
                'nivel' => 1,
                'pai_id' => null, // será atualizado abaixo
                'activo' => true,
            ],
            [
                'id' => Str::uuid(),
                'nome' => 'Aeroporto de Mavalane',
                'nivel' => 1,
                'pai_id' => null, // será atualizado abaixo
                'activo' => true,
            ],
            [
                'id' => Str::uuid(),
                'nome' => 'Controle do Zimpeto',
                'nivel' => 1,
                'pai_id' => null, // será atualizado abaixo
                'activo' => true,
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
