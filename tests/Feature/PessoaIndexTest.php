<?php

use App\Models\Pessoa;
use App\Models\SituacaoPessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function criarPessoaParaIndice(int $numero): Pessoa
{
    $pessoa = Pessoa::query()->create([
        'nomeCompleto' => "Agente {$numero}",
        'nip' => "NIP-INDICE-{$numero}",
    ]);

    SituacaoPessoa::query()->forceCreate([
        'pessoa_id' => $pessoa->id,
        'situacao' => 'Activo',
    ]);

    return $pessoa;
}

function inserirHistorico(string $tabela, string $pessoaId, string $chave, array $registos): void
{
    foreach ($registos as $registo) {
        DB::table($tabela)->insert([
            'id' => (string) Str::uuid(),
            'pessoa_id' => $pessoaId,
            $chave => $registo['valor'],
            'dataInicio' => $registo['inicio'],
            'dataFim' => $registo['fim'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
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

it('paginates unique people and returns only their current category and specialty', function () {
    $pessoas = collect(range(1, 12))->map(fn (int $numero) => criarPessoaParaIndice($numero));
    $pessoaComHistorico = $pessoas->first();

    inserirHistorico('categoria_policias', $pessoaComHistorico->id, 'categoria_id', [
        ['valor' => 'categoria-antiga', 'inicio' => today()->subYears(3), 'fim' => today()->subYear()],
        ['valor' => 'categoria-atual', 'inicio' => today()->subYear(), 'fim' => null],
        ['valor' => 'categoria-futura', 'inicio' => today()->addYear(), 'fim' => null],
    ]);

    inserirHistorico('especialidade_pessoas', $pessoaComHistorico->id, 'especialidade_id', [
        ['valor' => 'especialidade-antiga', 'inicio' => today()->subYears(3), 'fim' => today()->subYear()],
        ['valor' => 'especialidade-atual', 'inicio' => today()->subYear(), 'fim' => null],
        ['valor' => 'especialidade-futura', 'inicio' => today()->addYear(), 'fim' => null],
    ]);

    $primeiraPagina = $this->getJson('/api/pessoas?page=1&per_page=10')->assertOk();
    $segundaPagina = $this->getJson('/api/pessoas?page=2&per_page=10')->assertOk();
    $registos = collect($primeiraPagina->json('pessoas.data'))
        ->concat($segundaPagina->json('pessoas.data'));

    expect($primeiraPagina->json('pessoas.total'))->toBe(12)
        ->and($primeiraPagina->json('pessoas.last_page'))->toBe(2)
        ->and($segundaPagina->json('pessoas.data'))->toHaveCount(2)
        ->and($registos->pluck('id')->unique())->toHaveCount(12);

    $pessoa = $registos->firstWhere('id', $pessoaComHistorico->id);

    expect($pessoa['categoria_id'])->toBe('categoria-atual')
        ->and($pessoa['especialidade_id'])->toBe('especialidade-atual');
});

it('filters using only the category that covers the current date', function () {
    $pessoa = criarPessoaParaIndice(20);

    inserirHistorico('categoria_policias', $pessoa->id, 'categoria_id', [
        ['valor' => 'categoria-antiga', 'inicio' => today()->subYears(2), 'fim' => today()->subYear()],
        ['valor' => 'categoria-atual', 'inicio' => today()->subYear(), 'fim' => null],
    ]);

    $this->getJson('/api/pessoas?categoria=categoria-antiga')
        ->assertOk()
        ->assertJsonPath('pessoas.total', 0);

    $this->getJson('/api/pessoas?categoria=categoria-atual')
        ->assertOk()
        ->assertJsonPath('pessoas.total', 1)
        ->assertJsonPath('pessoas.data.0.id', $pessoa->id);
});

it('returns one search result with the current category, specialty and workplace', function () {
    $pessoa = Pessoa::query()->create([
        'nomeCompleto' => 'Agente Temporal',
        'nip' => 'NIP-INDICE-TEMPORAL',
    ]);

    SituacaoPessoa::query()->forceCreate([
        'pessoa_id' => $pessoa->id,
        'situacao' => 'Activo',
    ]);

    inserirHistorico('categoria_policias', $pessoa->id, 'categoria_id', [
        ['valor' => 'categoria-antiga', 'inicio' => today()->subYears(2), 'fim' => today()->subYear()],
        ['valor' => 'categoria-atual', 'inicio' => today()->subYear(), 'fim' => null],
    ]);

    inserirHistorico('especialidade_pessoas', $pessoa->id, 'especialidade_id', [
        ['valor' => 'especialidade-antiga', 'inicio' => today()->subYears(2), 'fim' => today()->subYear()],
        ['valor' => 'especialidade-atual', 'inicio' => today()->subYear(), 'fim' => null],
    ]);

    $localAntigo = (string) Str::uuid();
    $localAtual = (string) Str::uuid();

    foreach ([['id' => $localAntigo, 'nome' => 'Local antigo'], ['id' => $localAtual, 'nome' => 'Local atual']] as $local) {
        DB::table('locals')->insert($local + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    inserirHistorico('local_afectos', $pessoa->id, 'local_id', [
        ['valor' => $localAntigo, 'inicio' => today()->subYears(2), 'fim' => today()->subYear()],
        ['valor' => $localAtual, 'inicio' => today()->subYear(), 'fim' => null],
    ]);

    $this->getJson('/api/pessoas/search/Agente%20Temporal')
        ->assertOk()
        ->assertJsonCount(1, 'pessoas')
        ->assertJsonPath('pessoas.0.categoria_id', 'categoria-atual')
        ->assertJsonPath('pessoas.0.especialidade_id', 'especialidade-atual')
        ->assertJsonPath('pessoas.0.local_id', $localAtual);
});
