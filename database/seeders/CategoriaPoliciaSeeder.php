<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\CategoriaPolicia;
use App\Models\Categoria;
use App\Models\Pessoa;

class CategoriaPoliciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categorias = Categoria::take(3)->get();
        $pessoa = Pessoa::first();

        if ($pessoa && $categorias->count() >= 3) {
            CategoriaPolicia::create([
                'id' => (string) Str::uuid(),
                'categoria_id' => $categorias[0]->id,
                'pessoa_id' => $pessoa->id,
                'despacho' => '045/2025.301',
                'dataInicio' => '2025-01-01',
                'dataFim' => '2025-06-30',
                'obs' => 'Categoria atribuída por mérito',
            ]);

            CategoriaPolicia::create([
                'id' => (string) Str::uuid(),
                'categoria_id' => $categorias[1]->id,
                'pessoa_id' => $pessoa->id,
                'despacho' => '045/2025.302',
                'dataInicio' => '2025-07-01',
                'dataFim' => '2025-12-31',
                'obs' => 'Mudança de categoria por promoção',
            ]);

            CategoriaPolicia::create([
                'id' => (string) Str::uuid(),
                'categoria_id' => $categorias[2]->id,
                'pessoa_id' => $pessoa->id,
                'despacho' => '045/2025.303',
                'dataInicio' => '2026-01-01',
                'dataFim' => null,
                'obs' => 'Categoria em vigor',
            ]);
        }
    }
}
