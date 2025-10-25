<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Especialidade;
use Illuminate\Support\Str;

class EspecialidadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $especialidades = 
        [
            [
                'activo' => true,
                'descricao' => 'Especialidade Médica',
                'detalhes' => 'Área de cardiologia ',
            ],
            [
                'id' => (string) Str::uuid(),
                'activo' => true,
                'descricao' => 'Mecanica',
                'detalhes' => 'Electricista Auto',
            ],
            [
                'id' => (string) Str::uuid(),
                'activo' => true,
                'descricao' => 'Engenharia de Construcao Civil',
                'detalhes' => 'Reabilitação de edificios antigos',
            ]
        ];

        foreach ($especialidades as $data) {
            Especialidade::create([
                'id' => (string) Str::uuid(),
                ...$data,
            ]);
        }
    }
}
