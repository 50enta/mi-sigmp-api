<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormacaoRequest;
use App\Models\Escolaridade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EscolaridadeController extends Controller
{

    public function saveEscolaridade(StoreFormacaoRequest $request)
    {
        try {
            if ($request->file('formacoesComplementares.certificadoComplementar')) {
                $filename = time() . 'comple' . $request->file('formacoesComplementares.certificadoComplementar')->getClientOriginalName();
                $request->file('formacoesComplementares.certificadoComplementar')->move(public_path('uploads'), $filename);
            }

            if ($request->file('formacoesComplementares.certificado')) {
                $certificado = time() . 'certi' . $request->file('formacoesComplementares.certificado')->getClientOriginalName();
                $request->file('formacoesComplementares.certificado')->move(public_path('uploads'), $certificado);
            }

            $formacaoAcademica = DB::table('escolaridades') // substitua pelo nome real da sua tabela
                ->insertGetId([
                    'id' => (string) Str::uuid(),
                    'pessoa_id' => $request['formacaoAcademica']['pessoa_id'],
                    'nivel'       => $request['formacaoAcademica']['nivel'],
                    'instituicao' => $request['formacaoAcademica']['instituicao'],
                    'curso'       => $request['formacaoAcademica']['curso'],
                    'dataInicio'  => $request['formacaoAcademica']['dataInicio'],
                    'dataFim'     => isset($request['formacaoAcademica']['dataFim']) ? $request['formacaoAcademica']['dataFim'] : null,
                    'certificado' => $certificado ?? null,
                ]);

            //save nivel basico, matalane
            if (isset($request['formacaoPolicia']['basico'])) {
                $formacaoPolicialBasico = DB::table('formacaoPolicial')
                    ->insertGetId([
                        'id' => (string) Str::uuid(),
                        'pessoa_id' => $request['formacaoPolicia']['pessoa_id'],
                        'instituicao' => $request['formacaoPolicia']['basico'],
                        'curso' => isset($request['formacaoPolicia']['cursoBasico']['curso']) ? $request['formacaoPolicia']['cursoBasico']['curso'] : null,
                        'dataInicio' => $request['formacaoPolicia']['dataInicioBasico'],
                        'dataConclusao' => isset($request['formacaoPolicia']['dataConclusaoBasico']) ? $request['formacaoPolicia']['dataConclusaoBasico'] : null,
                    ]);
            }


            //save nivel medio, esapol
            if (isset($request['formacaoPolicia']['medio'])) {
                $formacaoPolicialMedio = DB::table('formacaoPolicial')
                    ->insertGetId([
                        'id' => (string) Str::uuid(),
                        'pessoa_id' => $request['formacaoPolicia']['pessoa_id'],
                        'instituicao' => $request['formacaoPolicia']['medio'],
                        'curso' => isset($request['formacaoPolicia']['cursoMedio']) ? $request['formacaoPolicia']['cursoMedio'] : null,
                        'dataInicio' => $request['formacaoPolicia']['dataInicioMedio'],
                        'dataConclusao' => isset($request['formacaoPolicia']['dataConclusaoMedio']) ? $request['formacaoPolicia']['dataConclusaoMedio'] : null
                    ]);
            }

            //save nivel superior, acipol
            if (isset($request['formacaoPolicia']['superior'])) {
                $formacaoPolicialSuperior = DB::table('formacaoPolicial')
                    ->insertGetId([
                        'id' => (string) Str::uuid(),
                        'pessoa_id' => $request['formacaoPolicia']['pessoa_id'],
                        'instituicao' => $request['formacaoPolicia']['superior'],
                        'curso' => isset($request['formacaoPolicia']['cursoSuperior']) ? $request['formacaoPolicia']['cursoSuperior'] : null,
                        'dataInicio' => $request['formacaoPolicia']['dataInicioSuperior'],
                        'dataConclusao' => isset($request['formacaoPolicia']['dataConclusaoSuperior']) ? $request['formacaoPolicia']['dataConclusaoSuperior'] : null,
                    ]);
            }

            if (isset($updateData['formacoesComplementares'])) {

                $formacoesComplementares = DB::table('formacoesComplementares')
                    ->insertGetId([
                        'id' => (string) Str::uuid(),
                        'pessoa_id' => $request['formacoesComplementares']['pessoa_id'],
                        'cursoComplementar' => $request['formacoesComplementares']['cursoComplementar'],
                        'instituicao' => $request['formacoesComplementares']['instituicao'],
                        'anoConlusao' => $request['formacoesComplementares']['anoConlusao'],
                        'certificado' => $filename ?? null
                    ]);
            }

            $pessoa = new PessoaController();
            $pessoa->updateStep(2, $request['formacaoAcademica']['pessoa_id']);

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
            dd($th);
            throw $th;
        }
    }

    public function index(Request $request)
    {
        try {
            $fAcademica = DB::table('escolaridades')
                ->orderBy('escolaridades.dataInicio', 'desc')
                ->where('escolaridades.pessoa_id', $request->query('pessoa_id'))
                ->select('*')
                ->get();

            $fPolicial = DB::table('formacaoPolicial')
                ->orderBy('formacaoPolicial.dataConclusao', 'desc')
                ->where('formacaoPolicial.pessoa_id', $request->query('pessoa_id'))
                ->select('*')
                ->get();

            $fComplementares = DB::table('formacoesComplementares')
                ->orderBy('formacoesComplementares.anoConlusao', 'desc')
                ->where('formacoesComplementares.pessoa_id', $request->query('pessoa_id'))
                ->select('*')
                ->get();

            return response()->json(['fAcademica' => $fAcademica, 'fPolicial' => $fPolicial, 'fComplementares' => $fComplementares], 200);
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['error' => 'Error inesperado'], 500);
        }
    }
}
