<?php

use App\Models\Pessoa;
use App\Models\Processos\Falecimentos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('registers a death and updates the agent situation', function () {
    $registrar = Pessoa::query()->create([
        'nomeCompleto' => 'Registador de falecimentos',
        'nip' => 'NIP-FALECIMENTO-REG',
    ]);
    $agent = Pessoa::query()->create([
        'nomeCompleto' => 'Agente falecido',
        'nip' => 'NIP-FALECIMENTO-001',
    ]);
    $user = User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $registrar->id,
        'acesso' => 'admin',
        'email' => 'falecimento@example.test',
        'password' => 'password',
    ]);
    Sanctum::actingAs($user);

    $response = $this->post('/api/falecimento', [
        'pessoa_id' => [$agent->id],
        'dataMorte' => '2026-08-20',
        'causaDaMorte' => 'Doença',
        'certidaoObito' => UploadedFile::fake()->create('certidao.pdf', 100, 'application/pdf'),
    ], ['Accept' => 'application/json'])->assertCreated();

    $death = Falecimentos::query()->firstOrFail();
    $path = public_path('uploads/'.$death->certidaoObito);

    try {
        $response->assertJsonPath('success', 'Falecimento registado com sucesso');
        expect($death->pessoa_id)->toBe($agent->id)
            ->and($death->abertoPor)->toBe($registrar->id)
            ->and(DB::table('situacao_pessoas')
                ->where('pessoa_id', $agent->id)
                ->where('situacao', 'Morto')
                ->exists())->toBeTrue();
        $this->assertFileExists($path);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});
