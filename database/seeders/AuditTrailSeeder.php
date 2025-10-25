<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuditTrailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pessoa = Pessoa::first(); // Assumes at least one Pessoa exists

        if ($pessoa) {
            AuditTrail::create([
                'id' => (string) Str::uuid(),
                'pessoa_id' => $pessoa->id,
                'info' => 'Primeiro registo de auditoria',
                'status' => 'OK',
            ]);
        }
    }
}
