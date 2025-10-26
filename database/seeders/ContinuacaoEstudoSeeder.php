<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ContinuacaoEstudo;
use App\Models\Pessoa;

class ContinuacaoEstudoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pessoa = Pessoa::first();

        if ($pessoa) {
            ContinuacaoEstudo::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'despacho' => '045/2025.501',
                'instituicao' => 'Universidade Eduardo Mondlane',
                'curso' => 'Administração Pública',
                'nivelPretendido' => 'Licenciatura',
                'situacao' => 'activo',
                'dataInicio' => '2025-02-01',
                'dataPrevisaoTermino' => '2028-01-31',
                'obs' => 'Curso em horário pós-laboral',
                'pessoa_id' => $pessoa->id,
            ]);

            ContinuacaoEstudo::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'despacho' => '045/2025.502',
                'instituicao' => 'ISCTEM',
                'curso' => 'Engenharia Informática',
                'nivelPretendido' => 'Mestrado',
                'situacao' => 'activo',
                'dataInicio' => '2025-03-15',
                'dataPrevisaoTermino' => '2027-03-15',
                'obs' => 'Curso financiado pela corporação',
                'pessoa_id' => $pessoa->id,
            ]);

            ContinuacaoEstudo::create([
                'id' => (string) Str::uuid(),
                'activo' => false,
                'despacho' => '045/2025.503',
                'instituicao' => 'Universidade Pedagógica',
                'curso' => 'Psicologia Educacional',
                'nivelPretendido' => 'Licenciatura',
                'situacao' => 'inactivo',
                'dataInicio' => '2024-01-10',
                'dataPrevisaoTermino' => '2027-12-10',
                'obs' => 'Estudos suspensos por motivos pessoais',
                'pessoa_id' => $pessoa->id,
            ]);
        }
    }
}
