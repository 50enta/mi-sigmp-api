<?php

use App\Models\Pessoa;
use App\Models\Processos\Ferias;
use App\Models\SituacaoPessoa;
use App\Models\User;
use App\Services\VacationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function vacationPerson(string $nip, string $createdAt = '2024-01-01'): Pessoa
{
    $person = Pessoa::query()->create(['nomeCompleto' => "Agente {$nip}", 'nip' => $nip]);
    $person->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->saveQuietly();

    return $person;
}

function authenticateVacationRegistrar(): Pessoa
{
    $registrar = vacationPerson('NIP-FERIAS-REG', '2020-01-01');
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'ferias@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    return $registrar;
}

function vacationPayload(Pessoa $person, string $number, string $start, string $end): array
{
    return [
        'nrProcesso' => $number,
        'pessoa_id' => [$person->id],
        'dataInicio' => $start,
        'dataFim' => $end,
    ];
}

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-08-15 10:00:00');
    authenticateVacationRegistrar();
});

afterEach(fn () => CarbonImmutable::setTestNow());

it('excludes national holidays when counting vacation days', function () {
    $service = app(VacationService::class);

    expect($service->countVacationDays('2026-09-01', '2026-09-30'))->toBe(28);
});

it('accumulates unused annual entitlement and records the remaining balance', function () {
    $person = vacationPerson('NIP-FERIAS-001');

    $this->getJson('/api/ferias/saldo?pessoa_id='.$person->id.'&year=2026')
        ->assertOk()
        ->assertJsonPath('data.balance', 0);

    $this->postJson('/api/ferias', vacationPayload($person, 'FER/001', '2026-08-01', '2026-08-30'))
        ->assertCreated()
        ->assertJsonPath('data.diasFerias', 30)
        ->assertJsonPath('data.saldoAntes', 90)
        ->assertJsonPath('data.saldoDepois', 60);

    $this->getJson('/api/ferias/saldo?pessoa_id='.$person->id.'&year=2026')
        ->assertOk()
        ->assertJsonPath('data.balance', 60);
});

it('accepts an existing legacy person identifier without requiring UUID format', function () {
    DB::table('pessoas')->insert([
        'id' => 'agente-legado-1',
        'nomeCompleto' => 'Agente legado',
        'nip' => 'NIP-FERIAS-LEGADO',
        'aprovado' => 1,
        'isGerivel' => true,
        'stepFinished' => 1,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);
    $person = Pessoa::query()->findOrFail('agente-legado-1');

    $this->postJson('/api/ferias', vacationPayload($person, 'FER/LEGADO', '2026-08-01', '2026-08-10'))
        ->assertCreated()
        ->assertJsonPath('data.pessoa_id', 'agente-legado-1');
});

it('rejects a vacation period longer than 30 chargeable days', function () {
    $person = vacationPerson('NIP-FERIAS-002');

    $this->postJson('/api/ferias', vacationPayload($person, 'FER/002', '2026-08-01', '2026-09-02'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('dataFim');
});

it('allows at most two vacation periods in the same year', function () {
    $person = vacationPerson('NIP-FERIAS-003');

    $this->postJson('/api/ferias', vacationPayload($person, 'FER/003-A', '2026-01-02', '2026-01-11'))->assertCreated();
    $this->postJson('/api/ferias', vacationPayload($person, 'FER/003-B', '2026-03-01', '2026-03-10'))->assertCreated();
    $this->postJson('/api/ferias', vacationPayload($person, 'FER/003-C', '2026-05-02', '2026-05-11'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('dataInicio');
});

it('rejects overlapping vacation periods', function () {
    $person = vacationPerson('NIP-FERIAS-004');

    $this->postJson('/api/ferias', vacationPayload($person, 'FER/004-A', '2026-06-01', '2026-06-15'))->assertCreated();
    $this->postJson('/api/ferias', vacationPayload($person, 'FER/004-B', '2026-06-10', '2026-06-20'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('dataInicio');
});

it('shows and filters the temporary Férias situation during the registered period', function () {
    $person = vacationPerson('NIP-FERIAS-ESTADO', '2026-01-01');
    SituacaoPessoa::query()->forceCreate([
        'pessoa_id' => $person->id,
        'situacao' => 'Activo',
    ]);
    $this->postJson('/api/ferias', vacationPayload($person, 'FER/ESTADO', '2026-08-10', '2026-08-20'))
        ->assertCreated();

    $this->getJson('/api/pessoas?situacao=ferias')
        ->assertOk()
        ->assertJsonPath('pessoas.total', 1)
        ->assertJsonPath('pessoas.data.0.situacao', 'Férias');

    $this->getJson('/api/pessoas?situacao=activo')
        ->assertOk()
        ->assertJsonPath('pessoas.total', 0);

    $this->getJson('/api/pessoas/search/Agente%20NIP-FERIAS-ESTADO')
        ->assertOk()
        ->assertJsonPath('pessoas.0.situacao', 'Férias');
});

it('counts active agents and agents who already took vacation in the selected year', function () {
    $active = vacationPerson('NIP-FERIAS-ATIVO', '2026-01-01');
    $completed = vacationPerson('NIP-FERIAS-GOZADAS', '2026-01-01');

    $this->postJson('/api/ferias', vacationPayload($active, 'FER/ATIVO', '2026-08-10', '2026-08-20'))->assertCreated();
    $this->postJson('/api/ferias', vacationPayload($completed, 'FER/GOZADAS', '2026-01-05', '2026-01-14'))->assertCreated();

    $this->getJson('/api/ferias/stats?year=2026')
        ->assertOk()
        ->assertJsonPath('data.agentesAtivos', 1)
        ->assertJsonPath('data.agentesQueGozaram', 1);
});

it('revalidates dates and recalculates days when a vacation is updated', function () {
    $person = vacationPerson('NIP-FERIAS-006', '2026-01-01');
    $response = $this->postJson('/api/ferias', vacationPayload($person, 'FER/006', '2026-09-01', '2026-09-10'))
        ->assertCreated();

    $this->putJson('/api/ferias/'.$response->json('data.id'), vacationPayload($person, 'FER/006', '2026-09-01', '2026-09-15'))
        ->assertOk()
        ->assertJsonPath('data.diasFerias', 14)
        ->assertJsonPath('data.saldoAntes', 30)
        ->assertJsonPath('data.saldoDepois', 16);
});

it('restores dynamically calculated balance when a vacation is removed', function () {
    $person = vacationPerson('NIP-FERIAS-005', '2026-01-01');
    $response = $this->postJson('/api/ferias', vacationPayload($person, 'FER/005', '2026-07-01', '2026-07-10'))
        ->assertCreated();

    $this->deleteJson('/api/ferias/'.$response->json('data.id'))->assertOk();
    $this->getJson('/api/ferias/saldo?pessoa_id='.$person->id.'&year=2026')
        ->assertOk()
        ->assertJsonPath('data.balance', 0);

    expect(Ferias::withTrashed()->count())->toBe(1);
});
