<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Curso;

class CursoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Curso::create([
            'id' => (string) Str::uuid(),
            'descricao' => 'Engenharia Informática',
            'dataInicio' => '2025-01-15',
            'especialidade' => 'Sistemas',
            'dataFim' => '2025-12-15',
            'numero' => 'CI2025',
            'grau' => 'Licenciatura',
            'local' => 'Maputo',
        ]);
    }
}
