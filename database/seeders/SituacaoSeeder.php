<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Situacao;

class SituacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Situacao::create([
            'id' => (string) Str::uuid(),
            'activo' => true,
            'situacao' => 'SUSPENSO',
            'obs' => 'Suspensão temporária por investigação interna, max 6 meses',
        ]);

        Situacao::create([
            'id' => (string) Str::uuid(),
            'activo' => true,
            'situacao' => 'APOSENTADO',
            'obs' => 'Aposentadoria por tempo de serviço',
        ]);

        Situacao::create([
            'id' => (string) Str::uuid(),
            'activo' => false,
            'situacao' => 'MORTO',
            'obs' => 'Falecimento confirmado',
        ]);

        Situacao::create([
            'id' => (string) Str::uuid(),
            'activo' => false,
            'situacao' => 'EXONERADO',
            'obs' => 'Quando perde o cargo',
        ]);

        Situacao::create([
            'id' => (string) Str::uuid(),
            'activo' => false,
            'situacao' => 'RESERVADO',
            'obs' => 'blá blá blá',
        ]);

        Situacao::create([
            'id' => (string) Str::uuid(),
            'activo' => false,
            'situacao' => 'EXPULSO',
            'obs' => 'Não mais faz parte da corporação',
        ]);
    }
}
