<?php

use App\Models\Local;
use App\Models\LocalAfecto;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('starts the new affiliation on the dispatch date', function () {
    $registrar = Pessoa::query()->create(['nomeCompleto' => 'Registador', 'nip' => 'NIP-REAF-REG']);
    $agent = Pessoa::query()->create(['nomeCompleto' => 'Agente reafectado', 'nip' => 'NIP-REAF-001']);
    $origin = Local::query()->create(['nome' => 'Origem da reafectação']);
    $destination = Local::query()->create(['nome' => 'Destino da reafectação']);
    $current = LocalAfecto::query()->create([
        'pessoa_id' => $agent->id,
        'local_id' => $origin->id,
        'dataInicio' => '2026-01-01',
    ]);
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'reafectacao@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/reafetar', [
        'pessoa_id' => [$agent->id],
        'origem' => $origin->id,
        'destino' => $destination->id,
        'motivo' => 'Necessidade de serviço',
        'cargo' => 'Chefe de secção',
        'nrDespacho' => 'DESP-REAF-001',
        'dataDespacho' => '2026-08-23',
    ])->assertCreated();

    expect($current->fresh()->dataFim)->toBe('2026-08-22');
    $this->assertDatabaseHas('local_afectos', [
        'pessoa_id' => $agent->id,
        'local_id' => $destination->id,
        'cargo' => 'Chefe de secção',
        'dataInicio' => '2026-08-23',
        'dataDespacho' => '2026-08-23',
        'isReafetacao' => true,
    ]);
});
