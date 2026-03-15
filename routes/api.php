<?php

use App\Http\Controllers\Session\sessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EscolaridadeController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\CategoriaEspecialidadeController;
use App\Http\Controllers\LocalAfectoController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\Processos\ExoneracaoController;
use App\Http\Controllers\Processos\ReafetacaoController;
use App\Http\Controllers\Processos\SituacaoDisciplinarController;
use App\Http\Controllers\Processos\TransferenciasController;

route::post('login', sessionController::class . '@login');

route::get('endpointTest', sessionController::class . '@enpointTest');

route::post('passwordRequest', sessionController::class . '@requestPassword');

route::post('checkToken', sessionController::class . '@checkToken')->middleware('auth:sanctum');

route::post('logout', sessionController::class . '@logout')->middleware('auth:sanctum');


Route::prefix('pessoas')->group(function () {
    Route::post('/', [PessoaController::class, 'store']);
    Route::get('/{id}', [PessoaController::class, 'show']);
    Route::get('/', [PessoaController::class, 'index']);
    Route::put('/{id}', [PessoaController::class, 'update']);
    Route::delete('/{id}', [PessoaController::class, 'destroy']);
    Route::get('/search/{query}', [PessoaController::class, 'search']);
});

Route::get('/dashboard-data', [PessoaController::class, 'getDashData']);

//Police registration
Route::post('/saveCatAndEsp', [CategoriaEspecialidadeController::class, 'saveCatAndEsp']);
Route::post('/escolaridades', [EscolaridadeController::class, 'saveEscolaridade']);
Route::post('/locaisAfectos', [LocalAfectoController::class, 'store']);

Route::get('/locais', [LocalController::class, 'index']);
Route::post('/locais', [LocalController::class, 'store']);
Route::delete('/locais/{id}', [LocalController::class, 'destroy']);
Route::put('/locais/{id}', [LocalController::class, 'update']);

Route::get('/getCatEspHistory', [CategoriaEspecialidadeController::class, 'getCatEspHistory']);


Route::get('/locaisAfectos', [LocalAfectoController::class, 'index']);
Route::get('/escolaridades', [EscolaridadeController::class, 'index']);

Route::prefix('disciplinar')->group(function () {
    Route::post('/', [SituacaoDisciplinarController::class, 'newProcess']);
    Route::get('/stats', [SituacaoDisciplinarController::class, 'stats']);
    Route::get('/{id}', [SituacaoDisciplinarController::class, 'show']);
    Route::get('/', [SituacaoDisciplinarController::class, 'index']);
    Route::put('/{id}', [SituacaoDisciplinarController::class, 'update']);
});

Route::prefix('exonerar')->group(function () {
    Route::post('/', [ExoneracaoController::class, 'newProcess']);
    Route::get('/stats', [ExoneracaoController::class, 'stats']);
    Route::get('/{id}', [ExoneracaoController::class, 'show']);
    Route::get('/', [ExoneracaoController::class, 'index']);
    Route::put('/{id}', [ExoneracaoController::class, 'update']);
});

Route::prefix('reafetar')->group(function () {
    Route::post('/', [ReafetacaoController::class, 'newProcess']);
    Route::get('/stats', [ReafetacaoController::class, 'stats']);
    Route::get('/{id}', [ReafetacaoController::class, 'show']);
    Route::get('/', [ReafetacaoController::class, 'index']);
    Route::put('/{id}', [ReafetacaoController::class, 'update']);
});

Route::prefix('transferir')->group(function () {
    Route::post('/', [TransferenciasController::class, 'newProcess']);
    Route::get('/stats', [TransferenciasController::class, 'stats']);
    Route::get('/{id}', [TransferenciasController::class, 'show']);
    Route::get('/', [TransferenciasController::class, 'index']);
    Route::put('/{id}', [TransferenciasController::class, 'update']);
});
//});
