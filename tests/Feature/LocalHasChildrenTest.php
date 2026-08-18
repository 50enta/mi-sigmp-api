<?php

use App\Models\Local;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createLocal(?string $parentId = null, string $name = 'Unidade'): Local
{
    return Local::create([
        'nome' => $name,
        'code' => strtoupper(substr($name, 0, 3)).'-'.uniqid(),
        'description' => null,
        'parent_id' => $parentId,
    ]);
}

test('has_children defaults to false and follows child creation and deletion', function () {
    $parent = createLocal(name: 'Unidade pai');

    expect($parent->fresh()->has_children)->toBeFalse();

    $child = createLocal($parent->id, 'Unidade filha');

    expect($parent->fresh()->has_children)->toBeTrue();

    $child->delete();

    expect($parent->fresh()->has_children)->toBeFalse();

    $child->restore();

    expect($parent->fresh()->has_children)->toBeTrue();
});

test('moving a child recalculates both parents', function () {
    $oldParent = createLocal(name: 'Pai anterior');
    $newParent = createLocal(name: 'Novo pai');
    $child = createLocal($oldParent->id, 'Filha móvel');

    $child->update(['parent_id' => $newParent->id]);

    expect($oldParent->fresh()->has_children)->toBeFalse()
        ->and($newParent->fresh()->has_children)->toBeTrue();
});
