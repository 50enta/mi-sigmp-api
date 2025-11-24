<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Local;

class LocalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run()
    {
        $pai = Local::create([
            'id' => (string) Str::uuid(),
            'activo' => true,
            'isLogico' => false,
            'nivel' => 1,
            'descricao' => 'Ministério do Interior',
            'comentarios' => 'Polícia da Repúplica de Moçambique',
            'hasPai' => false,
            'pai_id' => null,
        ]);

        Local::create([
            'id' => (string) Str::uuid(),
            'activo' => true,
            'isLogico' => true,
            'nivel' => 2,
            'descricao' => 'Comando Geral',
            'comentarios' => 'Polícia da Repúplica de Moçambique',
            'hasPai' => true,
            'pai_id' => $pai->id,
        ]);
    }
}
