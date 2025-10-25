<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\EspecialidadePessoa;
use App\Models\Especialidade;
use App\Models\Pessoa;

class EspecialidadePessoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pessoa = Pessoa::first();
        $especialidades = Especialidade::take(3)->get();

        if ($pessoa && $especialidades->count() >= 3) {
            EspecialidadePessoa::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'despacho' => '304/2025.194',
                'dataInicio' => '2025-01-01',
                'dataFim' => '2025-06-30',
                'isMudanca' => false,
                'especialidadeAnterior' => null,
                'especialidade_id' => $especialidades[0]->id,
                'pessoa_id' => $pessoa->id,
                'obs' => 'Primeira afectação',
            ]);

            EspecialidadePessoa::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'despacho' => '143/2025.024',
                'dataInicio' => '2025-07-01',
                'dataFim' => '2025-12-31',
                'isMudanca' => true,
                'especialidadeAnterior' => 'Cardiologia',
                'especialidade_id' => $especialidades[1]->id,
                'pessoa_id' => $pessoa->id,
                'obs' => 'Mudança por necessidade técnica',
            ]);

            EspecialidadePessoa::create([
                'id' => (string) Str::uuid(),
                'activo' => false,
                'despacho' => '003/2025.094',
                'dataInicio' => '2026-01-01',
                'dataFim' => null,
                'isMudanca' => true,
                'especialidadeAnterior' => 'Pediatria',
                'especialidade_id' => $especialidades[2]->id,
                'pessoa_id' => $pessoa->id,
                'obs' => 'Afectação suspensa por licença',
            ]);
        }
    }
}
