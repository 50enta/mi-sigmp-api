<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Pessoa;

class PessoaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pessoas = [
            [
                'dataNasc' => '1985-06-15',
                'nuit' => '123456789',
                'estadoCivil' => 'Casado',
                'sexo' => 'Masculino',
                'bi' => 'BI123456',
                'distrito' => 'Nampula',
                'provincia' => 'Nampula',
                'residencia' => 'Rua das Acácias, 45',
                'grupoSangue' => 'O+',
                'nrProcesso' => 'PROC001',
                'situacaoDisciplinar' => 'Sem ocorrências',
                'situacao' => 'Activo',
            ],
            [
                'dataNasc' => '1990-11-03',
                'nuit' => '987654321',
                'estadoCivil' => 'Solteiro',
                'sexo' => 'Feminino',
                'bi' => 'BI654321',
                'distrito' => 'Angoche',
                'provincia' => 'Nampula',
                'residencia' => 'Av. da Liberdade, 12',
                'grupoSangue' => 'A-',
                'nrProcesso' => 'PROC002',
                'situacaoDisciplinar' => 'Advertência verbal',
                'situacao' => 'Activo',
            ],
        ];

        foreach ($pessoas as $data) {
            Pessoa::create([
                'id' => (string) Str::uuid(),
                ...$data,
            ]);
        }
    }
}
