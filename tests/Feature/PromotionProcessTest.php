<?php

use App\Models\CategoriaPolicia;
use App\Models\Pessoa;
use App\Models\Processos\Promocao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('registers a promotion and starts the new category on the dispatch date', function () {
    $registrar = Pessoa::query()->create([
        'nomeCompleto' => 'Registador de promoções',
        'nip' => 'NIP-PROMO-REG',
    ]);
    $agent = Pessoa::query()->create([
        'nomeCompleto' => 'Agente promovido',
        'nip' => 'NIP-PROMO-001',
    ]);
    $currentCategory = CategoriaPolicia::query()->create([
        'pessoa_id' => $agent->id,
        'categoria_id' => '2',
        'dataInicio' => '2025-01-01',
    ]);
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'promocao@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/promover', [
        'pessoa_id' => [$agent->id],
        'categoriaActual' => '2',
        'novaCategoria' => '3',
        'modalidade' => 'Por orgânica',
        'nrDespacho' => '2345678',
        'dataDespacho' => '2026-08-02',
    ])
        ->assertCreated()
        ->assertJsonPath('success', 'Processo de promoção registado com sucesso');

    $promotion = Promocao::query()->firstOrFail();

    expect($promotion->pessoa_id)->toBe($agent->id)
        ->and($promotion->categoriaActual)->toBe('2')
        ->and($promotion->novaCategoria)->toBe('3')
        ->and($promotion->modalidade)->toBe('Por orgânica')
        ->and($promotion->dataDespacho)->toBe('2026-08-02')
        ->and((bool) $currentCategory->fresh()->activo)->toBeFalse()
        ->and($currentCategory->fresh()->dataFim)->toBe('2026-08-02');

    $this->assertDatabaseHas('categoria_policias', [
        'pessoa_id' => $agent->id,
        'categoria_id' => '3',
        'nrProcesso' => $promotion->systemId,
        'nrDespacho' => '2345678',
        'dataInicio' => '2026-08-02',
        'dataDespacho' => '2026-08-02',
        'activo' => true,
    ]);
});

it('requires observations for an exceptional promotion', function () {
    $registrar = Pessoa::query()->create([
        'nomeCompleto' => 'Registador de promoções excepcionais',
        'nip' => 'NIP-PROMO-EXC-REG',
    ]);
    $agent = Pessoa::query()->create([
        'nomeCompleto' => 'Agente promovido excepcionalmente',
        'nip' => 'NIP-PROMO-EXC-001',
    ]);
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'promocao-excepcional@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/promover', [
        'pessoa_id' => [$agent->id],
        'categoriaActual' => '2',
        'novaCategoria' => '3',
        'modalidade' => 'A título excepcional',
        'nrDespacho' => 'PROMO-EXC-001',
        'dataDespacho' => '2026-08-27',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('obs');
});
