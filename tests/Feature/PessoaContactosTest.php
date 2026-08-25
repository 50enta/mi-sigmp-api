<?php

use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => fake()->uuid(),
        'acesso' => 'admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    Sanctum::actingAs($user);
});

it('creates and returns an agent email and a dynamic list of phone numbers', function () {
    $pessoa = Pessoa::query()->create([
        'nip' => 'NIP-CONTACTOS-1',
        'nomeCompleto' => 'Agente com contactos',
    ]);

    $this->getJson("/api/pessoas/{$pessoa->id}/contactos")
        ->assertOk()
        ->assertJsonPath('contactos.email', null)
        ->assertJsonCount(0, 'contactos.telefones');

    $this->putJson("/api/pessoas/{$pessoa->id}/contactos", [
        'email' => 'agente@example.test',
        'telefones' => ['+258 84 000 0001', '+258 85 000 0002', '+258 86 000 0003', '+258 87 000 0004'],
    ])
        ->assertOk()
        ->assertJsonPath('contactos.email', 'agente@example.test')
        ->assertJsonCount(4, 'contactos.telefones')
        ->assertJsonPath('contactos.telefones.3', '+258 87 000 0004');

    $this->assertDatabaseHas('pessoas', [
        'id' => $pessoa->id,
        'email' => 'agente@example.test',
    ]);
    $this->assertDatabaseHas('pessoa_telefones', ['pessoa_id' => $pessoa->id, 'numero' => '+258 84 000 0001']);
    $this->assertDatabaseHas('pessoa_telefones', ['pessoa_id' => $pessoa->id, 'numero' => '+258 87 000 0004']);
});

it('updates the existing record and validates contact data', function () {
    $pessoa = Pessoa::query()->create([
        'nip' => 'NIP-CONTACTOS-2',
        'nomeCompleto' => 'Agente actualizado',
    ]);

    $url = "/api/pessoas/{$pessoa->id}/contactos";

    $this->putJson($url, ['email' => 'primeiro@example.test', 'telefones' => ['840000001']])->assertOk();
    $this->putJson($url, ['email' => 'segundo@example.test', 'telefones' => ['850000002']])
        ->assertOk()
        ->assertJsonPath('contactos.email', 'segundo@example.test')
        ->assertJsonPath('contactos.telefones.0', '850000002');

    $this->assertDatabaseCount('pessoa_telefones', 1);

    $this->putJson($url, ['email' => 'email-invalido', 'telefones' => ['840000001', '840000001']])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'telefones.1']);

    $this->putJson($url, ['email' => 'sem-contacto@example.test', 'telefones' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['telefones']);
});

it('stores contacts together with the first agent registration step', function () {
    $response = $this->postJson('/api/pessoas', [
        'info' => [
            'nomeCompleto' => 'Agente Registado',
            'dataNasc' => now()->subYears(25)->toDateString(),
            'nuit' => '123456789',
            'BI' => '123456789A',
            'provincia' => 'Gaza',
            'distrito' => 'Xai-Xai',
            'genero' => 'Masculino',
            'email' => ' registo@example.test ',
            'telefones' => [' 840000001 ', '850000002'],
        ],
    ])->assertCreated();

    $pessoaId = $response->json('pessoa.id');

    $this->assertDatabaseHas('pessoas', [
        'id' => $pessoaId,
        'email' => 'registo@example.test',
    ]);
    $this->assertDatabaseHas('pessoa_telefones', ['pessoa_id' => $pessoaId, 'numero' => '840000001']);
    $this->assertDatabaseHas('pessoa_telefones', ['pessoa_id' => $pessoaId, 'numero' => '850000002']);
});

it('rejects email and phone numbers already registered by another agent', function () {
    $firstPerson = Pessoa::query()->create([
        'nip' => 'NIP-CONTACTOS-UNIQUE-1',
        'nomeCompleto' => 'Primeiro Agente',
    ]);
    $secondPerson = Pessoa::query()->create([
        'nip' => 'NIP-CONTACTOS-UNIQUE-2',
        'nomeCompleto' => 'Segundo Agente',
    ]);

    $this->putJson("/api/pessoas/{$firstPerson->id}/contactos", [
        'email' => 'unico@example.test',
        'telefones' => ['840000010'],
    ])->assertOk();

    $this->putJson("/api/pessoas/{$secondPerson->id}/contactos", [
        'email' => 'unico@example.test',
        'telefones' => ['840000010'],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'telefones.0']);
});

it('rejects duplicate email and phone numbers during agent registration', function () {
    $existingPerson = Pessoa::query()->create([
        'nip' => 'NIP-CONTACTOS-UNIQUE-REGISTRATION',
        'nomeCompleto' => 'Agente Existente',
    ]);

    $this->putJson("/api/pessoas/{$existingPerson->id}/contactos", [
        'email' => 'registado@example.test',
        'telefones' => ['840000030'],
    ])->assertOk();

    $this->postJson('/api/pessoas', [
        'info' => [
            'nomeCompleto' => 'Novo Agente',
            'dataNasc' => now()->subYears(25)->toDateString(),
            'nuit' => '987654321',
            'BI' => '987654321A',
            'provincia' => 'Gaza',
            'distrito' => 'Xai-Xai',
            'genero' => 'Masculino',
            'email' => 'registado@example.test',
            'telefones' => ['840000030'],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['info.email', 'info.telefones.0']);
});

it('allows an agent to retain their own email and phone numbers', function () {
    $pessoa = Pessoa::query()->create([
        'nip' => 'NIP-CONTACTOS-UNIQUE-3',
        'nomeCompleto' => 'Agente sem alteração',
    ]);
    $url = "/api/pessoas/{$pessoa->id}/contactos";
    $payload = ['email' => 'proprio@example.test', 'telefones' => ['840000020']];

    $this->putJson($url, $payload)->assertOk();
    $this->putJson($url, $payload)->assertOk();
});
