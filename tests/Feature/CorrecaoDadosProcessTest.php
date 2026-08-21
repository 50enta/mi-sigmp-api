<?php

use App\Models\Pessoa;
use App\Models\Processos\CorrecaoDeDados;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a data correction with the correct alert and exposes its correction type', function () {
    $registrar = Pessoa::query()->create([
        'nomeCompleto' => 'Registador de correções',
        'nip' => 'NIP-CORRECAO-REG',
    ]);
    $agent = Pessoa::query()->create([
        'nomeCompleto' => 'Agente a corrigir',
        'nip' => 'NIP-CORRECAO-001',
    ]);
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'correcao@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    $response = $this->post('/api/corrigirDados', [
        'pessoa_id' => [$agent->id],
        'tipoCorrecao' => '0',
        'novoNome' => 'Nome corrigido',
        'motivoEobs' => 'Correção solicitada pelo agente.',
        'comprovativo' => UploadedFile::fake()->create('comprovativo.pdf', 100, 'application/pdf'),
    ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('success', 'Processo de correção de dados criado com sucesso!');

    $path = public_path('uploads/'.CorrecaoDeDados::query()->value('comprovativo'));

    try {
        $this->getJson('/api/corrigirDados')
            ->assertOk()
            ->assertJsonPath('data.data.0.tipoCorrecao', 0);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});
