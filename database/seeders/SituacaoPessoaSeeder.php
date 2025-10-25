<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\SituacaoPessoa;
use App\Models\Situacao;
use App\Models\Pessoa;

class SituacaoPessoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pessoa = Pessoa::first();
        $situacoes = Situacao::take(3)->get();

        if ($pessoa && $situacoes->count() >= 3) {
            SituacaoPessoa::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'situacao_id' => $situacoes[0]->id,
                'pessoa_id' => $pessoa->id,
                'isMudanca' => false,
                'despacho' => 'Despacho nº 101',
                'obs' => 'Situação inicial sem alterações',
            ]);

            SituacaoPessoa::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'situacao_id' => $situacoes[1]->id,
                'pessoa_id' => $pessoa->id,
                'isMudanca' => true,
                'despacho' => 'Despacho nº 102',
                'obs' => 'Mudança por decisão administrativa',
            ]);

            SituacaoPessoa::create([
                'id' => (string) Str::uuid(),
                'activo' => false,
                'situacao_id' => $situacoes[2]->id,
                'pessoa_id' => $pessoa->id,
                'isMudanca' => true,
                'despacho' => 'Despacho nº 103',
                'obs' => 'Situação encerrada por falecimento',
            ]);
        }
    }
}
