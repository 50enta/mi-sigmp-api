<?php

use App\Http\Controllers\Session\sessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactosController;
use App\Http\Controllers\EscalaoController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\LocalAfectoController;
use App\Http\Controllers\EspecialidadeController;
use App\Http\Controllers\EspecialidadePessoaController;
use App\Http\Controllers\SituacaoController;
use App\Http\Controllers\SituacaoPessoaController;
use App\Http\Controllers\CursoPoliciaController;
use App\Http\Controllers\CategoriaPoliciaController;
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
    
    Route::group([
        'prefix' => 'pessoas'
    ], function () {
        //pessoas
        Route::post('/', [PessoaController::class, 'store']);
        Route::get('/{id}', [PessoaController::class, 'show']);
        Route::get('/', [PessoaController::class, 'index']);
        Route::put('/{id}', [PessoaController::class, 'update']);
        Route::delete('/{id}', [PessoaController::class, 'destroy']);
    });


    Route::group([
        'prefix' => 'categorias'
    ], function () {
        //categorias
        Route::post('/', [CategoriaController::class, 'store']);
        Route::get('/{id}', [CategoriaController::class, 'show']);
        Route::get('/', action: [CategoriaController::class, 'index']);
        Route::put('/{id}', [CategoriaController::class, 'update']);
        Route::delete('/{id}', [CategoriaController::class, 'destroy']);
    });

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
        'prefix' => 'especialidades'
    ], function () {
        Route::post('/', [EspecialidadeController::class, 'store']);
        Route::get('/{id}', [EspecialidadeController::class, 'show']);
        Route::get('/', [EspecialidadeController::class, 'index']);
        Route::put('/{id}', [EspecialidadeController::class, 'update']);
        Route::delete('/{id}', [EspecialidadeController::class, 'destroy']);
    });

    Route::group([
        'prefix' => 'especialidade-pessoas'
    ], function () {
        Route::post('/', [EspecialidadePessoaController::class, 'store']);
        Route::get('/{id}', [EspecialidadePessoaController::class, 'show']);
        Route::get('/', [EspecialidadePessoaController::class, 'index']);
        Route::put('/{id}', [EspecialidadePessoaController::class, 'update']);
        Route::delete('/{id}', [EspecialidadePessoaController::class, 'destroy']);
    });


    Route::group([
        'prefix' => 'situacoes'
    ], function () {
        Route::post('/', [SituacaoController::class, 'store']);
        Route::get('/{id}', [SituacaoController::class, 'show']);
        Route::get('/', [SituacaoController::class, 'index']);
        Route::put('/{id}', [SituacaoController::class, 'update']);
        Route::delete('/{id}', [SituacaoController::class, 'destroy']);
    });

    Route::group([
        'prefix' => 'situacao-pessoas'
    ], function () {
        Route::post('/', [SituacaoPessoaController::class, 'store']);
        Route::get('/{id}', [SituacaoPessoaController::class, 'show']);
        Route::get('/', [SituacaoPessoaController::class, 'index']);
        Route::put('/{id}', [SituacaoPessoaController::class, 'update']);
        Route::delete('/{id}', [SituacaoPessoaController::class, 'destroy']);
    });


    Route::group([
        'prefix' => 'curso-policias'
    ], function () {
        Route::post('/', [CursoPoliciaController::class, 'store']);
        Route::get('/{id}', [CursoPoliciaController::class, 'show']);
        Route::get('/', [CursoPoliciaController::class, 'index']);
        Route::put('/{id}', [CursoPoliciaController::class, 'update']);
        Route::delete('/{id}', [CursoPoliciaController::class, 'destroy']);
    });


    Route::group([
        'prefix' => 'categoria-policias'
    ], function () {
        Route::post('/', [CategoriaPoliciaController::class, 'store']);
        Route::get('/{id}', [CategoriaPoliciaController::class, 'show']);
        Route::get('/', [CategoriaPoliciaController::class, 'index']);
        Route::put('/{id}', [CategoriaPoliciaController::class, 'update']);
        Route::delete('/{id}', [CategoriaPoliciaController::class, 'destroy']);
    });
    

    Route::group([
        'prefix' => 'situacao-disciplinares'
    ], function () {
        Route::post('/', [SituacaoDisciplinarController::class, 'store']);
        Route::get('/{id}', [SituacaoDisciplinarController::class, 'show']);
        Route::get('/', [SituacaoDisciplinarController::class, 'index']);
        Route::put('/{id}', [SituacaoDisciplinarController::class, 'update']);
        Route::delete('/{id}', [SituacaoDisciplinarController::class, 'destroy']);
    });

Route::group([
    'prefix' => 'escalao-policias'
], function () {
    Route::post('/', [EscalaoPoliciaController::class, 'store']);
    Route::get('/{id}', [EscalaoPoliciaController::class, 'show']);
    Route::get('/', [EscalaoPoliciaController::class, 'index']);
    Route::put('/{id}', [EscalaoPoliciaController::class, 'update']);
    Route::delete('/{id}', [EscalaoPoliciaController::class, 'destroy']);
});

Route::group([
    'prefix' => 'continuacao-estudos'
], function () {
    Route::post('/', [ContinuacaoEstudoController::class, 'store']);
    Route::get('/{id}', [ContinuacaoEstudoController::class, 'show']);
    Route::get('/', [ContinuacaoEstudoController::class, 'index']);
    Route::put('/{id}', [ContinuacaoEstudoController::class, 'update']);
    Route::delete('/{id}', [ContinuacaoEstudoController::class, 'destroy']);
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

//});




