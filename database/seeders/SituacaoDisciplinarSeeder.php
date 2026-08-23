<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Processos\SituacaoDisciplinar;
use App\Models\Pessoa;

class SituacaoDisciplinarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pessoa = Pessoa::first();

        if ($pessoa) {
            SituacaoDisciplinar::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'nrProcesso' => 'PROC-001',
                'pessoa_id' => $pessoa->id,
                'local' => 'Maputo',
                'proposta' => 'Suspensão preventiva por conduta inadequada',
                'abertoPor' => 'Chefe de Recursos Humanos',
                'dataAbertura' => '2025-01-10',
                'fechadoPor' => null,
                'dataFecho' => null,
                'obsFecho' => null,
                'aprovado' => 0,
                'aprovador' => null,
            ]);

            SituacaoDisciplinar::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'nrProcesso' => 'PROC-002',
                'pessoa_id' => $pessoa->id,
                'local' => 'Beira',
                'proposta' => 'Advertência formal por atraso recorrente',
                'abertoPor' => 'Supervisor Regional',
                'dataAbertura' => '2025-02-15',
                'fechadoPor' => 'Direcção Geral',
                'dataFecho' => '2025-03-01',
                'obsFecho' => 'Advertência aplicada e arquivada',
                'aprovado' => 1,
                'aprovador' => 'Director Nacional',
            ]);

            SituacaoDisciplinar::create([
                'id' => (string) Str::uuid(),
                'activo' => true,
                'nrProcesso' => 'PROC-005',
                'pessoa_id' => $pessoa->id,
                'local' => 'Beira',
                'proposta' => '6 meses de férias sem salário',
                'abertoPor' => 'Supervisor Regional (Valter Afonso)',
                'dataAbertura' => '2025-02-15',
                'fechadoPor' => 'José Maposse ',
                'dataFecho' => '2025-03-01',
                'obsFecho' => 'Advertência aplicada e arquivada',
                'aprovado' => 1,
                'aprovador' => 'Director Nacional',
            ]);
        }
    }
}
