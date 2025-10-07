<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class nearMissSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [1, 2, 3];
        $classes = ['A', 'B', 'C'];
        $severidade_levels = [1, 2, 3, 4, 5];
        $possiveisDanosOptions = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15',];

        for ($i = 1; $i <= 20; $i++) {
            DB::table('nearmisses')->insert([
                'reportado_por' => $severidade_levels[array_rand($severidade_levels)],
                'registado_por' => $severidade_levels[array_rand($severidade_levels)],
                'estado' => $estados[array_rand($estados)],
                'descricao' => "Incident description $i",
                'detalhes' => $i % 2 === 0 ? "Detailed report for event $i" : null,
                'dataAcontecimento' => now()->subDays(rand(1, 30))->addMinutes($i * 5),
                'classe' => $classes[array_rand($classes)],
                'medidas_imediatas' => "Immediate action taken for incident $i",
                'data_aprovacao' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                'quem_fechou' => $i % 3 === 0 ? "Closer $i" : null,
                'possiveisDanos' => json_encode(array_rand(array_flip($possiveisDanosOptions), rand(1, 14))),
                'local' => rand(1, 3),
                'local_reporter' => rand(1, 5),
                'responsaveis' => json_encode([rand(1, 5)]),
                'created_at' => now(),
                'updated_at' => now(),
                'regrasDeOuro' => json_encode(array_rand(array_flip($possiveisDanosOptions), rand(1, 14)))
            ]);
        }
    }
}
