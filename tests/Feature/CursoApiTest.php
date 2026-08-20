<?php

use App\Models\Curso;
use Database\Seeders\CursoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware();
});

function cursoPayload(array $overrides = []): array
{
    return array_merge([
        'descricao' => 'Curso Básico de Formação Policial',
        'dataInicio' => today()->addMonth()->toDateString(),
        'dataFim' => today()->addMonths(6)->toDateString(),
        'categoria' => 'basico',
        'numero_despacho' => '12/MI/2026',
        'data_despacho' => today()->toDateString(),
        'documento_despacho' => UploadedFile::fake()->create('despacho.pdf', 20, 'application/pdf'),
        'local' => 'Matalane',
        'especialidade' => 'Ordem e Segurança Pública',
        'total_esperado' => 250,
    ], $overrides);
}

test('regista curso e calcula o estado em curso a partir da data final', function () {
    $response = $this->post('/api/cursos', cursoPayload());

    $response
        ->assertCreated()
        ->assertJsonPath('curso.categoria', 'basico')
        ->assertJsonPath('curso.estado', 'em_curso')
        ->assertJsonPath('curso.cancelado', false)
        ->assertJsonPath('curso.total_esperado', 250);

    $curso = Curso::firstOrFail();

    $this->getJson('/api/cursos?paging=false')
        ->assertOk()
        ->assertJsonCount(1, 'cursos')
        ->assertJsonPath('cursos.0.id', $curso->id);

    @unlink(public_path('uploads/'.$curso->documento_despacho));
});

test('valida os campos obrigatorios do curso', function () {
    $this->postJson('/api/cursos', [])
        ->assertStatus(409)
        ->assertJsonValidationErrors([
            'descricao',
            'dataInicio',
            'dataFim',
            'categoria',
            'numero_despacho',
            'data_despacho',
            'documento_despacho',
            'local',
        ]);
});

test('actualiza cancela lista cancelados e elimina um curso', function () {
    $created = $this->post('/api/cursos', cursoPayload())->assertCreated()->json('curso');

    $this->putJson('/api/cursos/'.$created['id'], [
        'descricao' => 'Curso Básico Actualizado',
        'dataInicio' => today()->subYear()->toDateString(),
        'dataFim' => today()->subDay()->toDateString(),
        'categoria' => 'basico',
        'numero_despacho' => '13/MI/2026',
        'data_despacho' => today()->toDateString(),
        'local' => 'Matalane',
        'total_esperado' => 275,
    ])->assertOk()
        ->assertJsonPath('curso.total_esperado', 275)
        ->assertJsonPath('curso.estado', 'finalizado');

    $this->getJson('/api/cursos?paging=false&estado=finalizado')->assertJsonCount(1, 'cursos');
    $this->getJson('/api/cursos?paging=false&estado=em_curso')->assertJsonCount(0, 'cursos');

    $this->patchJson('/api/cursos/'.$created['id'].'/cancelar')
        ->assertOk()
        ->assertJsonPath('curso.estado', 'cancelado');

    $this->getJson('/api/cursos?paging=false&cancelado=false')->assertJsonCount(0, 'cursos');
    $this->getJson('/api/cursos/cancelados?paging=false')
        ->assertJsonCount(1, 'cursos')
        ->assertJsonPath('cursos.0.id', $created['id']);

    $this->deleteJson('/api/cursos/'.$created['id'])->assertOk();
    $this->assertSoftDeleted('cursos', ['id' => $created['id']]);

    @unlink(public_path('uploads/'.$created['documento_despacho']));
});

test('o seed de cursos pode ser executado novamente sem duplicar registos', function () {
    $this->seed(CursoSeeder::class);
    $this->seed(CursoSeeder::class);

    expect(Curso::count())->toBe(6)
        ->and(Curso::where('categoria', 'basico')->count())->toBe(2)
        ->and(Curso::where('categoria', 'medio')->count())->toBe(2)
        ->and(Curso::where('categoria', 'superior')->count())->toBe(2);

    $this->assertDatabaseHas('cursos', [
        'descricao' => '10º Curso de Formação Básica/2026',
        'cancelado' => false,
    ]);
});
