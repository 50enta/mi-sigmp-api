<?php

use App\Models\Local;
use App\Models\LocalAfecto;
use App\Models\Pessoa;
use App\Models\Processos\Transferencias;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('stores the selected agent UUID when creating a transfer', function () {
    $registrar = Pessoa::query()->create([
        'nomeCompleto' => 'Registador de transferências',
        'nip' => 'NIP-TRANSFERENCIA-REG',
    ]);
    $agent = Pessoa::query()->create([
        'nomeCompleto' => 'Agente transferido',
        'nip' => 'NIP-TRANSFERENCIA-001',
    ]);
    $origin = Local::query()->create(['nome' => 'Unidade de origem']);
    $destination = Local::query()->create(['nome' => 'Unidade de destino']);
    $currentAffiliation = LocalAfecto::query()->create([
        'pessoa_id' => $agent->id,
        'local_id' => $origin->id,
        'dataInicio' => '2026-01-01',
    ]);
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'transferencia@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/transferir', [
        'pessoa_id' => [$agent->id],
        'origem' => $origin->id,
        'destino' => $destination->id,
        'regime' => 'pedido',
        'nrDespacho' => 'DESP-TRF-001',
        'dataDespacho' => '2026-08-23',
    ])->assertCreated()
        ->assertJsonPath('success', 'Transferência criada com sucesso!');

    $transfer = Transferencias::query()->firstOrFail();

    expect($transfer->pessoa_id)->toBe($agent->id)
        ->and($transfer->origem)->toBe($origin->id)
        ->and($transfer->destino)->toBe($destination->id)
        ->and($currentAffiliation->fresh()->dataFim)->toBe('2026-08-22');

    $this->assertDatabaseHas('local_afectos', [
        'pessoa_id' => $agent->id,
        'local_id' => $destination->id,
        'dataInicio' => '2026-08-23',
        'isTransferencia' => true,
    ]);
});

it('swaps both current affiliations when the transfer regime is permuta', function () {
    $registrar = Pessoa::query()->create(['nomeCompleto' => 'Registador', 'nip' => 'NIP-TRF-REG-2']);
    $agent = Pessoa::query()->create(['nomeCompleto' => 'Primeiro agente', 'nip' => 'NIP-TRF-002']);
    $permutator = Pessoa::query()->create(['nomeCompleto' => 'Membro da permuta', 'nip' => 'NIP-TRF-003']);
    $origin = Local::query()->create(['nome' => 'Primeira unidade']);
    $destination = Local::query()->create(['nome' => 'Segunda unidade']);
    LocalAfecto::query()->create(['pessoa_id' => $agent->id, 'local_id' => $origin->id, 'dataInicio' => '2026-01-01']);
    LocalAfecto::query()->create(['pessoa_id' => $permutator->id, 'local_id' => $destination->id, 'dataInicio' => '2026-01-01']);
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'permuta@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/transferir', [
        'pessoa_id' => [$agent->id],
        'permutador' => [$permutator->id],
        'origem' => $origin->id,
        'destino' => $destination->id,
        'regime' => 'permuta',
        'nrDespacho' => 'DESP-TRF-002',
        'dataDespacho' => '2026-08-23',
        'nrDespachoPermutador' => 'DESP-TRF-003',
        'dataDespachoPermutador' => '2026-08-24',
    ])->assertCreated();

    $this->assertDatabaseHas('local_afectos', [
        'pessoa_id' => $agent->id,
        'local_id' => $destination->id,
        'dataInicio' => '2026-08-23',
    ]);
    $this->assertDatabaseHas('local_afectos', [
        'pessoa_id' => $permutator->id,
        'local_id' => $origin->id,
        'dataInicio' => '2026-08-23',
        'dataDespacho' => '2026-08-24',
    ]);
});
