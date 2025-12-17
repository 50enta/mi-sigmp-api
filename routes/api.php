<?php

use App\Http\Controllers\Session\sessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactosController;
use App\Http\Controllers\EscalaoController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\CategoriaEspecialidadeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\LocalAfectoController;
use App\Http\Controllers\SituacaoController;
use App\Http\Controllers\SituacaoPessoaController;
use App\Http\Controllers\SituacaoDisciplinarController;
use App\Http\Controllers\EscalaoPoliciaController;
use App\Http\Controllers\ContinuacaoEstudoController;
use App\Http\Controllers\EscolaridadeController;


route::post('login', sessionController::class . '@login');

route::post('passwordRequest', sessionController::class . '@requestPassword');

route::post('checkToken', sessionController::class . '@checkToken')->middleware('auth:sanctum');

route::post('logout', sessionController::class . '@logout')->middleware('auth:sanctum');


//Route::middleware('auth:api')->group(function () {

Route::group([
    'prefix' => 'contactos'
], function () {
    //contactos
    Route::post('/', [ContactosController::class, 'store']);
    Route::get('/{id}', [ContactosController::class, 'show']);
    Route::get('/', [ContactosController::class, 'index']);
    Route::put('/{id}', [ContactosController::class, 'update']);
    Route::delete('/{id}', [ContactosController::class, 'destroy']);
});

Route::group([
    'prefix' => 'escaloes'
], function () {
    //escaloes
    Route::post('/', [EscalaoController::class, 'store']);
    Route::get('/{id}', [EscalaoController::class, 'show']);
    Route::get('/', [EscalaoController::class, 'index']);
    Route::put('/{id}', [EscalaoController::class, 'update']);
    Route::delete('/{id}', [EscalaoController::class, 'destroy']);
});

Route::prefix('pessoas')->group(function () {
    Route::post('/', [PessoaController::class, 'store']);
    Route::get('/{id}', [PessoaController::class, 'show']);
    Route::get('/', [PessoaController::class, 'index']);
    Route::put('/{id}', [PessoaController::class, 'update']);
    Route::delete('/{id}', [PessoaController::class, 'destroy']);
});

Route::get('/dashboard-data', [PessoaController::class, 'getDashData']);


Route::group([
    'prefix' => 'audittrails'
], function () {
    Route::post('/', [AuditTrailController::class, 'store']);
    Route::get('/{id}', [AuditTrailController::class, 'show']);
    Route::get('/', [AuditTrailController::class, 'index']);
    Route::put('/{id}', [AuditTrailController::class, 'update']);
    Route::delete('/{id}', [AuditTrailController::class, 'destroy']);
});

Route::group([
    'prefix' => 'cursos'
], function () {
    Route::post('/', [CursoController::class, 'store']);
    Route::get('/{id}', [CursoController::class, 'show']);
    Route::get('/', [CursoController::class, 'index']);
    Route::put('/{id}', [CursoController::class, 'update']);
    Route::delete('/{id}', [CursoController::class, 'destroy']);
});


Route::group([
    'prefix' => 'locais'
], function () {
    Route::post('/', [LocalController::class, 'store']);
    Route::get('/{id}', [LocalController::class, 'show']);
    Route::get('/', [LocalController::class, 'index']);
    Route::put('/{id}', [LocalController::class, 'update']);
    Route::delete('/{id}', [LocalController::class, 'destroy']);
});

Route::group([
    'prefix' => 'local-afectos'
], function () {
    Route::post('/', [LocalAfectoController::class, 'store']);
    Route::get('/{id}', [LocalAfectoController::class, 'show']);
    Route::get('/', [LocalAfectoController::class, 'index']);
    Route::put('/{id}', [LocalAfectoController::class, 'update']);
    Route::delete('/{id}', [LocalAfectoController::class, 'destroy']);
});


Route::group([
    'prefix' => 'escolaridades'
], function () {
    Route::post('/', [EscolaridadeController::class, 'store']);
    Route::get('/{id}', [EscolaridadeController::class, 'show']);
    Route::get('/', [EscolaridadeController::class, 'index']);
    Route::put('/{id}', [EscolaridadeController::class, 'update']);
    Route::delete('/{id}', [EscolaridadeController::class, 'destroy']);
});

Route::post('/saveCatAndEsp', [CategoriaEspecialidadeController::class, 'saveCatAndEsp']);
//});
