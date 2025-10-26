<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Pessoa;
use App\Models\AuditTrail;


class AuditTrailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pessoa = Pessoa::first(); 

        if ($pessoa) {
            AuditTrail::create([
                'id' => (string) Str::uuid(),
                'pessoa_id' => $pessoa->id,
                'info' => 'Criacao do registo',
                'status' => 'Sucesso',
            ]);

            AuditTrail::create([
                'id' => (string) Str::uuid(),
                'pessoa_id' => $pessoa->id,
                'info' => 'Actualização de nome:  de Xaver Bamo para Xavier Bamo',
                'status' => 'Sucesso',
            ]);

            AuditTrail::create([
                'id' => (string) Str::uuid(),
                'pessoa_id' => $pessoa->id,
                'info' => 'Promoção de carreira: de Básico para Superior',
                'status' => 'Sucesso',
            ]);

            AuditTrail::create([
                'id' => (string) Str::uuid(),
                'pessoa_id' => $pessoa->id,
                'info' => 'Eliminação do registo',
                'status' => 'Sucesso',
            ]);
        }
    }
}
