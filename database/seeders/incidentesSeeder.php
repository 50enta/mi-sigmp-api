<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class incidentesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [1, 2, 3];
        $classes = ['A', 'B', 'C'];
        $possiveisDanosOptions = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15',];

        for ($i = 1; $i <= 20; $i++) {
            DB::table('incidentes')->insert([
                'estado' => $estados[array_rand($estados)],
                'descricao' => "Incident description #$i",
                'circunstancias' => $i % 2 === 0 ? "Circumstance details for case #$i" : null,
                'reportado_por' => rand(1, 10),
                'registado_por' => rand(1, 10),
                'dataAcontecimento' => now()->subDays(rand(5, 50))->addMinutes($i * 3),
                'classe' => $classes[array_rand($classes)],
                'com_arquivada' => rand(0, 1),
                'com_arquivada_cta' => rand(0, 1),
                'com_arquivada_data' => rand(0, 1) ? now()->subDays(rand(10, 40)) : null,
                'com_arquivada_cta_data' => rand(0, 1) ? now()->subDays(rand(20, 50)) : null,
                'medidas_imediatas' => "Immediate action taken for incident #$i",
                'causas_preliminares' => $i % 3 === 0 ? "Preliminary cause info for case #$i" : null,
                'dm_samcol' => $i % 2 === 0 ? "Material damage for SAMCOL #$i" : null,
                'dm_terceiros' => $i % 3 === 0 ? "Third-party material damage for case #$i" : null,
                'dm_contratados' => $i % 4 === 0 ? "Contractor material damage for case #$i" : null,
                'local' => rand(1, 3),
                'local_reporter' => rand(1, 4),
                'data_aprovacao' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                'quem_fechou' => rand(1, 10),
                'severidade' => rand(1, 5),
                'tipo_incidente' => rand(1, 10),
                'autores' => "Author $i",
                'created_at' => now(),
                'updated_at' => now(),
                'regrasDeOuro' => json_encode(array_rand(array_flip($possiveisDanosOptions), rand(1, 14)))
            ]);
        }
    }
}
