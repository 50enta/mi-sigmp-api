<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\CursoPolicia;
use App\Models\Curso;
use App\Models\Pessoa;

class CursoPoliciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $cursos = Curso::take(3)->get();
        $pessoa = Pessoa::first();

        if ($pessoa && $cursos->count() >= 3) {
            CursoPolicia::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'curso_id' => $cursos[0]->id,
                'pessoa_id' => $pessoa->id,
                'despacho_admissao' => '045/2025.201',
                'dataInicio' => '2025-03-01',
                'dataFim' => '2025-09-30',
            ]);

            CursoPolicia::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'curso_id' => $cursos[1]->id,
                'pessoa_id' => $pessoa->id,
                'despacho_admissao' => '045/2025.202',
                'dataInicio' => '2025-04-15',
                'dataFim' => '2025-10-15',
            ]);

            CursoPolicia::create([
                'id' => (string) Str::uuid(),
                'activo' => false,
                'curso_id' => $cursos[2]->id,
                'pessoa_id' => $pessoa->id,
                'despacho_admissao' => '045/2025.203',
                'dataInicio' => '2025-05-01',
                'dataFim' => '2025-11-30',
            ]);
        }
    }
}
