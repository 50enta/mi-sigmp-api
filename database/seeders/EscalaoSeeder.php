<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Escalao;

class EscalaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $escaloes = [
            ['descricao' => 'B1', 'descricao' => 'Básico 1', 'comentarios' => 'Escalao de bozassi', 'activo' => true],
            ['descricao' => 'M1', 'descricao' => 'Médio 1', 'comentarios' => 'Escalao cinrivn', 'activo' => true],
            ['descricao' => 'S1', 'descricao' => 'Superior 1', 'comentarios' => 'escalo xyz', 'activo' => true],
        ];

        foreach ($escaloes as $data) {
            Escalao::create([
                'id' => (string) Str::uuid(),
                ...$data,
            ]);
        }
    }
}
