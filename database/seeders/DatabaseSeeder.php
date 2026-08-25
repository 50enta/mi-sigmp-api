<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            EscalaoSeeder::class,
            PessoaTableSeeder::class,
            CategoriaSeeder::class,
            AuditTrailSeeder::class,
            CursoSeeder::class,
            LocalSeeder::class,
            LocalAfectoSeeder::class,
            EspecialidadeSeeder::class,
            EspecialidadePessoaSeeder::class,
            SituacaoSeeder::class,
            SituacaoPessoaSeeder::class,
            CursoPoliciaSeeder::class,
            CategoriaPoliciaSeeder::class,
            SituacaoDisciplinarSeeder::class,
            EscalaoPoliciaSeeder::class,
            ContinuacaoEstudoSeeder::class,
            EscolaridadeSeeder::class,

        ]);
    }

    // public function run(): void
    // {
    //     // User::factory(10)->create();

    //     User::factory()->create([
    //         'name' => 'Test User',
    //         'email' => 'test@example.com',
    //     ]);
    // }
}
