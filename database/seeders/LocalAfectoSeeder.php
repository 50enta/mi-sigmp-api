<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\LocalAfecto;
use App\Models\Local;
use App\Models\Pessoa;

class LocalAfectoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $local = Local::first();
        $pessoa = Pessoa::first();

        if ($local && $pessoa) {
            LocalAfecto::create([
                'id' => (string) Str::uuid(),
                'local_id' => $local->id,
                'pessoa_id' => $pessoa->id,
                'despacho' => 'Despacho nº 456/2025',
                'dataInicio' => '2025-02-01',
                'dataFim' => '2025-12-31',
                'isTransferencia' => true,
                'transferidor_id' => null,
                'aprovador' => 'Direcção Geral',
                'aprovado' => 1,
                'local_origem' => 'hashcode-local-anterior',
                'regime' => 'Permuta',
                'permutador' => 'João M.',
            ]);
        }
    }
}
