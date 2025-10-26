<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\EscalaoPolicia;
use App\Models\Escalao;
use App\Models\Pessoa;

class EscalaoPoliciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $escalas = Escalao::take(3)->get();
        $pessoa = Pessoa::first();

        if ($pessoa && $escalas->count() >= 3) {
            EscalaoPolicia::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'escala_id' => $escalas[0]->id,
                'pessoa_id' => $pessoa->id,
                'despacho' => 'Despacho nº 401',
                'dataInicio' => '2025-01-01',
                'dataFim' => '2025-06-30',
                'obs' => 'Escala atribuída por necessidade operacional',
            ]);

            EscalaoPolicia::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'escala_id' => $escalas[1]->id,
                'pessoa_id' => $pessoa->id,
                'despacho' => 'Despacho nº 402',
                'dataInicio' => '2025-07-01',
                'dataFim' => '2025-12-31',
                'obs' => 'Rotação semestral',
            ]);

            EscalaoPolicia::create([
                'id' => (string) Str::uuid(),
                'activo' => false,
                'escala_id' => $escalas[2]->id,
                'pessoa_id' => $pessoa->id,
                'despacho' => 'Despacho nº 403',
                'dataInicio' => '2026-01-01',
                'dataFim' => null,
                'obs' => 'Escala suspensa por licença médica',
            ]);
        }
    }
}
