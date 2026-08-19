<?php

use App\Http\Controllers\AuditSettingController;
use App\Http\Controllers\CategoriaEspecialidadeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\EscolaridadeController;
use App\Http\Controllers\LocalAfectoController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\Processos\ActualizacaoNivelAcademicoController;
use App\Http\Controllers\Processos\AssentoBiograficoController;
use App\Http\Controllers\Processos\ContinuacaoEstudoController;
use App\Http\Controllers\Processos\CorrecaoDeDadosController;
use App\Http\Controllers\Processos\ExoneracaoController;
use App\Http\Controllers\Processos\FalecimentosController;
use App\Http\Controllers\Processos\FeriasController;
use App\Http\Controllers\Processos\PensoesController;
use App\Http\Controllers\Processos\ProcessRecordController;
use App\Http\Controllers\Processos\PromocaoController;
use App\Http\Controllers\Processos\ReafetacaoController;
use App\Http\Controllers\Processos\SituacaoDisciplinarController;
use App\Http\Controllers\Processos\SubsideosFunebresController;
use App\Http\Controllers\Processos\TransferenciasController;
use App\Http\Controllers\Session\sessionController;
use Illuminate\Support\Facades\Route;

Route::post('login', [sessionController::class, 'login'])->middleware('throttle:5,1');

Route::get('endpointTest', [sessionController::class, 'endpointTest']);

Route::post('passwordRequest', [sessionController::class, 'requestPassword'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'audit'])->group(function () {
    Route::get('user', [sessionController::class, 'user']);

    Route::post('checkToken', [sessionController::class, 'checkToken']);

    Route::post('logout', [sessionController::class, 'logout']);

    Route::get('audit-settings', [AuditSettingController::class, 'show']);
    Route::put('audit-settings', [AuditSettingController::class, 'update']);
    Route::get('audit-logs', [AuditSettingController::class, 'logs']);

    Route::prefix('pessoas')->group(function () {
        Route::post('/', [PessoaController::class, 'store']);
        Route::get('/{id}', [PessoaController::class, 'show']);
        Route::get('/', [PessoaController::class, 'index']);
        Route::put('/{id}', [PessoaController::class, 'update']);
        Route::delete('/{id}', [PessoaController::class, 'destroy']);
        Route::get('/search/{query}', [PessoaController::class, 'search']);
    });

    Route::get('/dashboard-data', [PessoaController::class, 'getDashData']);

    // Police registration
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

    Route::get('/cursos/cancelados', [CursoController::class, 'cancelled']);
    Route::patch('/cursos/{id}/cancelar', [CursoController::class, 'cancel']);
    Route::apiResource('/cursos', CursoController::class);

    Route::prefix('disciplinar')->group(function () {
        Route::post('/', [SituacaoDisciplinarController::class, 'newProcess']);
        Route::get('/stats', [SituacaoDisciplinarController::class, 'stats']);
        Route::get('/{id}', [SituacaoDisciplinarController::class, 'show']);
        Route::get('/', [SituacaoDisciplinarController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'disciplinar');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'disciplinar');
    });

    Route::prefix('exonerar')->group(function () {
        Route::post('/', [ExoneracaoController::class, 'newProcess']);
        Route::get('/stats', [ExoneracaoController::class, 'stats']);
        Route::get('/{id}', [ExoneracaoController::class, 'show']);
        Route::get('/', [ExoneracaoController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'exonerar');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'exonerar');
    });

    Route::prefix('reafetar')->group(function () {
        Route::post('/', [ReafetacaoController::class, 'newProcess']);
        Route::get('/stats', [ReafetacaoController::class, 'stats']);
        Route::get('/{id}', [ReafetacaoController::class, 'show']);
        Route::get('/', [ReafetacaoController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'reafetar');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'reafetar');
    });

    Route::prefix('transferir')->group(function () {
        Route::post('/', [TransferenciasController::class, 'newProcess']);
        Route::get('/stats', [TransferenciasController::class, 'stats']);
        Route::get('/{id}', [TransferenciasController::class, 'show']);
        Route::get('/', [TransferenciasController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'transferir');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'transferir');
    });

    Route::prefix('continuarEstudos')->group(function () {
        Route::post('/', [ContinuacaoEstudoController::class, 'newProcess']);
        Route::get('/stats', [ContinuacaoEstudoController::class, 'stats']);
        Route::get('/{id}', [ContinuacaoEstudoController::class, 'show']);
        Route::get('/', [ContinuacaoEstudoController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'continuarEstudos');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'continuarEstudos');
    });

    Route::prefix('atualizacaoAcademica')->group(function () {
        Route::post('/', [ActualizacaoNivelAcademicoController::class, 'newProcess']);
        Route::get('/stats', [ActualizacaoNivelAcademicoController::class, 'stats']);
        Route::get('/{id}', [ActualizacaoNivelAcademicoController::class, 'show']);
        Route::get('/', [ActualizacaoNivelAcademicoController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'atualizacaoAcademica');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'atualizacaoAcademica');
    });

    Route::prefix('promover')->group(function () {
        Route::post('/', [PromocaoController::class, 'newProcess']);
        Route::get('/stats', [PromocaoController::class, 'stats']);
        Route::get('/{id}', [PromocaoController::class, 'show']);
        Route::get('/', [PromocaoController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'promover');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'promover');
    });

    Route::prefix('corrigirDados')->group(function () {
        Route::post('/', [CorrecaoDeDadosController::class, 'newProcess']);
        Route::get('/stats', [CorrecaoDeDadosController::class, 'stats']);
        Route::get('/{id}', [CorrecaoDeDadosController::class, 'show']);
        Route::get('/', [CorrecaoDeDadosController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'corrigirDados');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'corrigirDados');
    });

    Route::prefix('falecimento')->group(function () {
        Route::post('/', [FalecimentosController::class, 'newProcess']);
        Route::get('/stats', [FalecimentosController::class, 'stats']);
        Route::get('/{id}', [FalecimentosController::class, 'show']);
        Route::get('/', [FalecimentosController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'falecimento');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'falecimento');
    });

    Route::prefix('reservaAposentadoActivo')->group(function () {
        Route::post('/', [PensoesController::class, 'newProcess']);
        Route::get('/stats', [PensoesController::class, 'stats']);
        Route::get('/{id}', [PensoesController::class, 'show']);
        Route::get('/', [PensoesController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'reservaAposentadoActivo');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'reservaAposentadoActivo');
    });

    Route::prefix('subsidioFunebre')->group(function () {
        Route::post('/', [SubsideosFunebresController::class, 'newProcess']);
        Route::get('/stats', [SubsideosFunebresController::class, 'stats']);
        Route::get('/{id}', [SubsideosFunebresController::class, 'show']);
        Route::get('/', [SubsideosFunebresController::class, 'index']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'subsidioFunebre');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'subsidioFunebre');
    });

    Route::prefix('assentoBiografico')->group(function () {
        Route::post('/', [AssentoBiograficoController::class, 'newProcess']);
        Route::get('/stats', [AssentoBiograficoController::class, 'stats']);
        Route::get('/', [AssentoBiograficoController::class, 'index']);
        Route::get('/{id}', [AssentoBiograficoController::class, 'show']);
        Route::put('/{id}', [ProcessRecordController::class, 'update'])->defaults('process', 'assentoBiografico');
        Route::delete('/{id}', [ProcessRecordController::class, 'destroy'])->defaults('process', 'assentoBiografico');
    });

    Route::prefix('ferias')->group(function () {
        Route::post('/', [FeriasController::class, 'newProcess']);
        Route::get('/stats', [FeriasController::class, 'stats']);
        Route::get('/saldo', [FeriasController::class, 'balance']);
        Route::get('/', [FeriasController::class, 'index']);
        Route::get('/{id}', [FeriasController::class, 'show']);
        Route::put('/{id}', [FeriasController::class, 'update']);
        Route::delete('/{id}', [FeriasController::class, 'destroy']);
    });
});
