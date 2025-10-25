<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Contactos;

class ContactosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contactos = [
            [
                'nip' => '123456',
                'contactoPrincipal' => '258841234567',
                'contactoAlternativo' => '258842345678',
                'contactoEmergencia' => '258843456789',
            ],
            [
                'nip' => '789012',
                'contactoPrincipal' => '258845678901',
                'contactoAlternativo' => '258846789012',
                'contactoEmergencia' => '258847890123',
            ],
        ];

        foreach ($contactos as $data) {
            Contactos::create([
                'id' => (string) Str::uuid(),
                ...$data,
            ]);
        }
    }
}
