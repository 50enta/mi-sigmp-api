<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Escolaridade;
use App\Models\Pessoa;

class EscolaridadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pessoa = Pessoa::first();

        if ($pessoa) {
            Escolaridade::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'nivel' => 'medio',
                'instituicao' => 'Escola Secundária Josina Machel',
                'curso' => 'Ciências Naturais',
                'dataInicio' => '2015-01-01',
                'dataFim' => '2017-12-31',
                'isConcluido' => true,
                'obs' => 'Curso concluído com distinção',
                'pessoa_id' => $pessoa->id,
            ]);

            Escolaridade::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'nivel' => 'licenciatura',
                'instituicao' => 'Universidade Eduardo Mondlane',
                'curso' => 'Engenharia Civil',
                'dataInicio' => '2018-02-01',
                'dataFim' => '2022-01-31',
                'isConcluido' => true,
                'obs' => 'Participou em projectos de extensão universitária',
                'pessoa_id' => $pessoa->id,
            ]);

            Escolaridade::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'nivel' => 'mestrado',
                'instituicao' => 'ISCTEM',
                'curso' => 'Gestão de Projectos',
                'dataInicio' => '2023-03-01',
                'dataFim' => null,
                'isConcluido' => false,
                'obs' => 'Em curso, previsão de conclusão em 2025',
                'pessoa_id' => $pessoa->id,
            ]);
        }
    }
}
