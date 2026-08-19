<?php

namespace Database\Seeders;

use App\Models\Curso;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $cursos = [
            [
                'descricao' => '10º Curso de Formação Básica/2026',
                'dataInicio' => '2026-01-12',
                'dataFim' => '2026-12-18',
                'categoria' => 'basico',
                'numero_despacho' => '10/CFB/2026',
                'documento_despacho' => 'seed/despacho-10-cfb-2026.pdf',
                'local' => 'Matalane',
                'especialidade' => null,
                'total_esperado' => 500,
            ],
            [
                'descricao' => '9º Curso de Formação Básica/2025',
                'dataInicio' => '2025-01-13',
                'dataFim' => '2025-12-19',
                'categoria' => 'basico',
                'numero_despacho' => '09/CFB/2025',
                'documento_despacho' => 'seed/despacho-09-cfb-2025.pdf',
                'local' => 'Matalane',
                'especialidade' => null,
                'total_esperado' => 450,
            ],
            [
                'descricao' => '5º Curso de Formação de Sargentos/2025',
                'dataInicio' => '2025-02-03',
                'dataFim' => '2025-11-28',
                'categoria' => 'medio',
                'numero_despacho' => '05/CFS/2025',
                'documento_despacho' => 'seed/despacho-05-cfs-2025.pdf',
                'local' => 'ESAPOL',
                'especialidade' => null,
                'total_esperado' => 180,
            ],
            [
                'descricao' => '6º Curso de Formação de Sargentos/2026',
                'dataInicio' => '2026-02-02',
                'dataFim' => '2026-11-27',
                'categoria' => 'medio',
                'numero_despacho' => '06/CFS/2026',
                'documento_despacho' => 'seed/despacho-06-cfs-2026.pdf',
                'local' => 'ESAPOL',
                'especialidade' => null,
                'total_esperado' => 200,
            ],
            [
                'descricao' => '18º Curso de Formação de Oficiais/2025',
                'dataInicio' => '2025-02-10',
                'dataFim' => '2025-12-12',
                'categoria' => 'superior',
                'numero_despacho' => '18/CFO/2025',
                'documento_despacho' => 'seed/despacho-18-cfo-2025.pdf',
                'local' => 'ACIPOL',
                'especialidade' => null,
                'total_esperado' => 120,
            ],
            [
                'descricao' => '19º Curso de Formação de Oficiais/2026',
                'dataInicio' => '2026-02-09',
                'dataFim' => '2026-12-11',
                'categoria' => 'superior',
                'numero_despacho' => '19/CFO/2026',
                'documento_despacho' => 'seed/despacho-19-cfo-2026.pdf',
                'local' => 'ACIPOL',
                'especialidade' => null,
                'total_esperado' => 130,
            ],
        ];

        foreach ($cursos as $curso) {
            Curso::updateOrCreate(
                ['descricao' => $curso['descricao']],
                [...$curso, 'cancelado' => false],
            );
        }
    }
}
