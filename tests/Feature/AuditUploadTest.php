<?php

use App\Http\Middleware\AuditRequests;
use App\Models\AuditTrail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

it('audits an upload after the controller moves the temporary file', function () {
    $file = UploadedFile::fake()->create('despacho.pdf', 10, 'application/pdf');
    $request = Request::create(
        '/api/saveCatAndEsp',
        'POST',
        ['categoria' => ['pessoa_id' => '10', 'password' => 'segredo']],
        [],
        ['categoria' => ['despachoCat' => $file]],
    );
    $request->setUserResolver(fn () => (object) ['pessoa_id' => '10']);

    $destination = storage_path('framework/testing/audit-upload');
    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }

    $response = (new AuditRequests())->handle($request, function (Request $request) use ($destination) {
        $request->file('categoria.despachoCat')->move($destination, 'despacho.pdf');

        return response()->json(['success' => true], 201);
    });

    expect($response->getStatusCode())->toBe(201);

    $audit = AuditTrail::query()->latest()->firstOrFail();
    expect($audit->metadata['files']['categoria']['despachoCat'])->toBe('despacho.pdf')
        ->and($audit->metadata['input']['categoria']['password'])->toBe('[REDACTED]');

    @unlink($destination . DIRECTORY_SEPARATOR . 'despacho.pdf');
});
