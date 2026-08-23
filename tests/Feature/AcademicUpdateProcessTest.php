<?php

use App\Models\Escolaridade;
use App\Models\Pessoa;
use App\Models\Processos\ActualizacaoNivelAcademico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $registrar = Pessoa::query()->create([
        'nomeCompleto' => 'Registador académico',
        'nip' => 'NIP-ACADEMICO-REG',
    ]);
    $agent = Pessoa::query()->create([
        'nomeCompleto' => 'Agente académico',
        'nip' => 'NIP-ACADEMICO-001',
    ]);
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'academico@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    $this->academicAgent = $agent;
});

it('requires the academic completion date', function () {
    $this->post('/api/atualizacaoAcademica', [
        'pessoa_id' => [$this->academicAgent->id],
        'nivel' => 'Licenciatura',
        'instituicao' => 'Universidade Eduardo Mondlane',
        'curso' => 'Direito',
        'certificado' => UploadedFile::fake()->create('certificado.pdf', 100, 'application/pdf'),
    ], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('dataDeConclusao');

    expect(ActualizacaoNivelAcademico::query()->count())->toBe(0)
        ->and(Escolaridade::query()->count())->toBe(0);
});

it('registers an academic update with its completion date', function () {

    $response = $this->post('/api/atualizacaoAcademica', [
        'pessoa_id' => [$this->academicAgent->id],
        'nivel' => 'Licenciatura',
        'instituicao' => 'Universidade Eduardo Mondlane',
        'curso' => 'Direito',
        'dataDeConclusao' => '2026-08-20',
        'certificado' => UploadedFile::fake()->create('certificado.pdf', 100, 'application/pdf'),
    ], ['Accept' => 'application/json'])->assertCreated();

    $process = ActualizacaoNivelAcademico::query()->firstOrFail();
    $education = Escolaridade::query()->firstOrFail();
    $path = public_path('uploads/'.$process->certificado);

    try {
        $response->assertJsonPath('success', 'Processo de atualização académica criado com sucesso!');
        expect($process->pessoa_id)->toBe($this->academicAgent->id)
            ->and($process->dataDeConclusao)->toBe('2026-08-20')
            ->and($education->pessoa_id)->toBe($this->academicAgent->id)
            ->and($education->dataFim)->toBe('2026-08-20');
        $this->assertFileExists($path);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});
