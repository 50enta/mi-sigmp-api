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
            'activo' => true,
            'aprovado' => 1,
            'nip' => '221997001',
            'isGerivel' => true,
            'nomeCompleto' => 'Carlos Mucavele',
            'nomeMae' => 'Maria Mucavele',
            'nomePai' => 'João Mucavele',
            'dataNasc' => '1990-05-12',
            'nuit' => '123456789',
            'estadoCivil' => 'casado',
            'grupoSangue' => 'O+',
            'distrito' => 'Matola',
            'provincia' => 'Maputo Cidade',
            'residencia' => 'Bairro Fomento',
            'genero' => 'masculino',
            'BI' => '123456789MZ',
            'altura' => 1.75,
            'linguas' => 'Português, Changana',
           ],
           [
            'activo' => true,
            'aprovado' => 1,
            'nip' => '221997034',
            'isGerivel' => true,
            'nomeCompleto' => 'Julia Susngaio',
            'nomeMae' => 'Maria Mucavele',
            'nomePai' => 'João Pisoio',
            'dataNasc' => '1990-05-12',
            'nuit' => '123456789',
            'estadoCivil' => 'casado',
            'grupoSangue' => 'O+',
            'distrito' => 'Matola',
            'provincia' => 'Maputo provincía',
            'residencia' => 'Bairro Fomento',
            'genero' => 'masculino',
            'BI' => '123456789MZ',
            'altura' => 1.75,
            'linguas' => 'Português, Ingles',
           ],
           [
            'activo' => true,
            'aprovado' => 1,
            'nip' => '231997078',
            'isGerivel' => true,
            'nomeCompleto' => 'Valter Assane',
            'nomeMae' => 'Maria Mucavele',
            'nomePai' => 'João Pisoio',
            'dataNasc' => '1990-05-12',
            'nuit' => '123456789',
            'estadoCivil' => 'Solteiro',
            'grupoSangue' => 'A-',
            'distrito' => 'Angoche',
            'provincia' => 'Niassa',
            'residencia' => 'Bairro abcd',
            'genero' => 'masculino',
            'BI' => '123456789MG',
            'altura' => 1.75,
            'linguas' => 'Português, Ingles',
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
