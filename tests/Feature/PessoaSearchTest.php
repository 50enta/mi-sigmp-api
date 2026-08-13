<?php

use App\Models\Pessoa;
use App\Models\SituacaoPessoa;
use App\Models\User;
use App\Services\ProcessAgentEligibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function pessoaComSituacao(string $nome, string $nip, string $situacao): Pessoa
{
    $pessoa = Pessoa::query()->create([
        'nomeCompleto' => $nome,
        'nip' => $nip,
    ]);

    SituacaoPessoa::query()->forceCreate([
        'pessoa_id' => $pessoa->id,
        'situacao' => $situacao,
    ]);

    return $pessoa;
}

function criarContinuacaoEmAndamento(Pessoa $pessoa): void
{
    DB::table('continuacao_estudos')->insert([
        'nrProcesso' => 'PROC-TESTE',
        'estado' => 'aberto',
        'despacho' => 'despacho.pdf',
        'instituicao' => 'Instituicao de Teste',
        'curso' => 'Curso de Teste',
        'nivelPretendido' => 'Licenciatura',
        'pessoa_id' => $pessoa->id,
        'abertoPor' => $pessoa->id,
        'data' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

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

it('always returns the current agent situation', function () {
    pessoaComSituacao('Agente Activo', 'NIP20001', 'Activo');
    pessoaComSituacao('Agente Morto', 'NIP20002', 'Morto');

    $this->getJson('/api/pessoas/search/Agente')
        ->assertOk()
        ->assertJsonCount(2, 'pessoas')
        ->assertJsonPath('pessoas.0.situacao', 'Activo')
        ->assertJsonPath('pessoas.1.situacao', 'Morto');
});

it('blocks dead and exonerated agents from exoneration', function () {
    pessoaComSituacao('Agente Activo', 'NIP20003', 'Activo');
    pessoaComSituacao('Agente Exonerado', 'NIP20004', 'Exonerado');
    pessoaComSituacao('Agente Morto', 'NIP20005', 'Morto');

    $response = $this->getJson('/api/pessoas/search/Agente?processo=exonerar')->assertOk();

    expect(collect($response->json('pessoas'))->pluck('selecaoBloqueada', 'situacao')->all())
        ->toMatchArray(['Activo' => false, 'Exonerado' => true, 'Morto' => true]);
});

it('blocks dead agents from funeral subsidy', function () {
    pessoaComSituacao('Agente Activo', 'NIP20006', 'Activo');
    pessoaComSituacao('Agente Morto', 'NIP20007', 'Morto');

    $response = $this->getJson('/api/pessoas/search/Agente?processo=subsidioFunebre')->assertOk();

    expect(collect($response->json('pessoas'))->pluck('selecaoBloqueada', 'situacao')->all())
        ->toMatchArray(['Activo' => false, 'Morto' => true]);
});

it('blocks agents with an ongoing study continuation process', function () {
    $disponivel = pessoaComSituacao('Agente Disponivel', 'NIP20008', 'Activo');
    $ocupado = pessoaComSituacao('Agente Ocupado', 'NIP20009', 'Activo');
    criarContinuacaoEmAndamento($ocupado);

    $response = $this->getJson('/api/pessoas/search/Agente?processo=continuarEstudos')->assertOk();
    $pessoas = collect($response->json('pessoas'))->keyBy('id');

    expect($pessoas[$disponivel->id]['selecaoBloqueada'])->toBeFalse()
        ->and($pessoas[$ocupado->id]['selecaoBloqueada'])->toBeTrue()
        ->and($pessoas[$ocupado->id]['motivoBloqueio'])->toContain('em andamento');
});

it('rejects unsupported process filters', function () {
    $this->getJson('/api/pessoas/search/Agente?processo=desconhecido')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('processo');
});

it('enforces the same eligibility rules when a process is created', function () {
    $morto = pessoaComSituacao('Agente Morto', 'NIP20010', 'Morto');
    $exonerado = pessoaComSituacao('Agente Exonerado', 'NIP20011', 'Exonerado');
    $estudante = pessoaComSituacao('Agente Estudante', 'NIP20012', 'Activo');
    criarContinuacaoEmAndamento($estudante);
    $eligibility = app(ProcessAgentEligibility::class);

    expect(fn () => $eligibility->ensureEligible(ProcessAgentEligibility::EXONERAR, $morto->id))
        ->toThrow(ValidationException::class)
        ->and(fn () => $eligibility->ensureEligible(ProcessAgentEligibility::EXONERAR, $exonerado->id))
        ->toThrow(ValidationException::class)
        ->and(fn () => $eligibility->ensureEligible(ProcessAgentEligibility::SUBSIDIO_FUNEBRE, $morto->id))
        ->toThrow(ValidationException::class)
        ->and(fn () => $eligibility->ensureEligible(ProcessAgentEligibility::CONTINUAR_ESTUDOS, $estudante->id))
        ->toThrow(ValidationException::class);
});

it('opens a study continuation process without requiring a digital dispatch file', function () {
    $registrar = pessoaComSituacao('Agente Registador', 'NIP20013', 'Activo');
    $agente = pessoaComSituacao('Agente Estudante Novo', 'NIP20014', 'Activo');

    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/continuarEstudos', [
        'pessoa_id' => [$agente->id],
        'instituicao' => 'Instituicao de Teste',
        'curso' => 'Curso de Teste',
        'nivelPretendido' => 'Licenciatura',
        'data' => now()->toDateString(),
        'nrProcesso' => 'PROC-SEM-FICHEIRO',
        'nrDespacho' => 'DESP-001',
        'dataDespacho' => now()->toDateString(),
    ])->assertCreated();

    $this->assertDatabaseHas('continuacao_estudos', [
        'pessoa_id' => $agente->id,
        'nrProcesso' => 'PROC-SEM-FICHEIRO',
        'estado' => 'aberto',
        'despacho' => null,
    ]);
});
