<?php

use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function statefulHeaders(): array
{
    return [
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3001',
        'Referer' => 'http://localhost:3001/',
    ];
}

function sessionUser(): User
{
    $pessoa = Pessoa::query()->create([
        'nip' => 'NIP10000',
        'nomeCompleto' => 'Utilizador de Teste',
    ]);

    return User::query()->forceCreate([
        'activo' => true,
        'ja_acedeu' => true,
        'pessoa_id' => $pessoa->id,
        'acesso' => 'admin',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
}

it('rejects unauthenticated access to protected API routes', function () {
    $this->getJson('/api/user')->assertUnauthorized();
    $this->getJson('/api/pessoas')->assertUnauthorized();
});

it('authenticates with a secure HttpOnly session cookie instead of a bearer token', function () {
    $user = sessionUser();

    $response = $this
        ->withHeaders(statefulHeaders())
        ->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonPath('user.id', $user->pessoa_id);

    expect($response->json('token'))->toBeNull();

    $sessionCookie = collect($response->headers->getCookies())
        ->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

    expect($sessionCookie)->not->toBeNull()
        ->and($sessionCookie->isHttpOnly())->toBeTrue();

    $this->withHeaders(statefulHeaders())
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('user.email', $user->email);

    $this->withHeaders(statefulHeaders())
        ->postJson('/api/logout')
        ->assertNoContent();

    $this->withHeaders(statefulHeaders())
        ->getJson('/api/user')
        ->assertUnauthorized();
});
