<?php

use App\Models\Curso;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware();
});

function pessoaParaFormacao(): Pessoa
{
    return Pessoa::create([
        'nip' => 'NIP-'.uniqid(),
        'nomeCompleto' => 'Agente em Formação',
    ]);
}

test('nao grava a etapa sem formacao academica nem policial', function () {
    $pessoa = pessoaParaFormacao();

    $this->postJson('/api/escolaridades', [
        'formacoesComplementares' => [
            'pessoa_id' => $pessoa->id,
            'cursoComplementar' => 'Informática',
            'instituicaoComplementar' => 'Instituição',
            'anoConclusaoComplementar' => 2026,
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('formacaoAcademica');

    $this->assertDatabaseCount('escolaridades', 0);
    $this->assertDatabaseCount('formacaoPolicial', 0);
    expect($pessoa->fresh()->stepFinished)->toBe(1);
});

test('grava apenas formacao academica', function () {
    $pessoa = pessoaParaFormacao();

    $this->postJson('/api/escolaridades', [
        'formacaoAcademica' => [
            'pessoa_id' => $pessoa->id,
            'nivel' => 'licenciatura',
            'curso' => 'Direito',
            'instituicao' => 'Universidade',
            'dataInicio' => '2021-02-01',
            'dataFim' => '2025-12-01',
        ],
    ])->assertCreated();

    $this->assertDatabaseCount('escolaridades', 1);
    $this->assertDatabaseCount('formacaoPolicial', 0);
    expect($pessoa->fresh()->stepFinished)->toBe(2);
});

test('grava apenas uma formacao policial', function () {
    $pessoa = pessoaParaFormacao();
    $curso = Curso::create([
        'descricao' => '10º Curso de Formação Básica/2026',
        'dataInicio' => '2026-01-12',
        'dataFim' => '2026-12-18',
        'categoria' => 'basico',
        'numero_despacho' => '10/CFB/2026',
        'documento_despacho' => 'seed/despacho.pdf',
        'local' => 'Matalane',
    ]);

    $this->postJson('/api/escolaridades', [
        'formacaoPolicia' => [
            'pessoa_id' => $pessoa->id,
            'basico' => 'matalane',
            'cursoBasico' => $curso->id,
            'dataInicioBasico' => '2026-01-12',
            'dataConclusaoBasico' => '2026-12-18',
        ],
    ])->assertCreated();

    $this->assertDatabaseCount('escolaridades', 0);
    $this->assertDatabaseHas('formacaoPolicial', [
        'pessoa_id' => $pessoa->id,
        'curso_id' => $curso->id,
    ]);
    expect($pessoa->fresh()->stepFinished)->toBe(2);
});
