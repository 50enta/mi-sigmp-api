<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categorias = [
            ['descricao' => 'Oficial General', 'comentarios' => 'Categoria de bozassi', 'activo' => true],
            ['descricao' => 'Oficial Superior', 'comentarios' => 'Categoria abc', 'activo' => true],
            ['descricao' => 'Sargento', 'comentarios' => 'Categoria dxyz', 'activo' => false],
        ];

        foreach ($categorias as $data) {
            Categoria::create([
                'id' => (string) Str::uuid(),
                ...$data,
            ]);
        }
    }
}
