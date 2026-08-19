<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormacaoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EscolaridadeController extends Controller
{
    public function saveEscolaridade(StoreFormacaoRequest $request)
    {
        try {
            DB::beginTransaction();
            if ($request->file('formacoesComplementares.certificadoComplementar')) {
                $filename = time().'comple'.$request->file('formacoesComplementares.certificadoComplementar')->getClientOriginalName();
                $request->file('formacoesComplementares.certificadoComplementar')->move(public_path('uploads'), $filename);
            }

            if ($request->has('formacaoAcademica') && $request->file('formacaoAcademica.certificado')) {
                $certificado = time().'certi'.$request->file('formacaoAcademica.certificado')->getClientOriginalName();
                $request->file('formacaoAcademica.certificado')->move(public_path('uploads'), $certificado);
            }

            if ($request->has('formacaoAcademica')) {
                DB::table('escolaridades')->insert([
                    'id' => (string) Str::uuid(),
                    'pessoa_id' => $request['formacaoAcademica']['pessoa_id'],
                    'nivel' => Str::ucfirst($request['formacaoAcademica']['nivel']),
                    'instituicao' => $request['formacaoAcademica']['instituicao'],
                    'curso' => $request['formacaoAcademica']['curso'] ?? null,
                    'dataInicio' => $request['formacaoAcademica']['dataInicio'],
                    'dataFim' => isset($request['formacaoAcademica']['dataFim']) ? $request['formacaoAcademica']['dataFim'] : null,
                    'certificado' => $certificado ?? null,
                ]);
            }

            // save nivel basico, matalane
            if (isset($request['formacaoPolicia']['basico'])) {
                $cursoBasico = isset($request['formacaoPolicia']['cursoBasico'])
                    ? \App\Models\Curso::find($request['formacaoPolicia']['cursoBasico'])
                    : null;
                $formacaoPolicialBasico = DB::table('formacaoPolicial')
                    ->insertGetId([
                        'id' => (string) Str::uuid(),
                        'pessoa_id' => $request['formacaoPolicia']['pessoa_id'],
                        'instituicao' => $request['formacaoPolicia']['basico'],
                        'curso' => $cursoBasico?->descricao,
                        'curso_id' => $cursoBasico?->id,
                        'dataInicio' => $request['formacaoPolicia']['dataInicioBasico'],
                        'dataConclusao' => isset($request['formacaoPolicia']['dataConclusaoBasico']) ? $request['formacaoPolicia']['dataConclusaoBasico'] : null,
                    ]);
            }

            // save nivel medio, esapol
            if (isset($request['formacaoPolicia']['medio'])) {
                $cursoMedio = isset($request['formacaoPolicia']['cursoMedio'])
                    ? \App\Models\Curso::find($request['formacaoPolicia']['cursoMedio'])
                    : null;
                $formacaoPolicialMedio = DB::table('formacaoPolicial')
                    ->insertGetId([
                        'id' => (string) Str::uuid(),
                        'pessoa_id' => $request['formacaoPolicia']['pessoa_id'],
                        'instituicao' => $request['formacaoPolicia']['medio'],
                        'curso' => $cursoMedio?->descricao,
                        'curso_id' => $cursoMedio?->id,
                        'dataInicio' => $request['formacaoPolicia']['dataInicioMedio'],
                        'dataConclusao' => isset($request['formacaoPolicia']['dataConclusaoMedio']) ? $request['formacaoPolicia']['dataConclusaoMedio'] : null,
                    ]);
            }

            // save nivel superior, acipol
            if (isset($request['formacaoPolicia']['superior'])) {
                $cursoSuperior = isset($request['formacaoPolicia']['cursoSuperior'])
                    ? \App\Models\Curso::find($request['formacaoPolicia']['cursoSuperior'])
                    : null;
                $formacaoPolicialSuperior = DB::table('formacaoPolicial')
                    ->insertGetId([
                        'id' => (string) Str::uuid(),
                        'pessoa_id' => $request['formacaoPolicia']['pessoa_id'],
                        'instituicao' => $request['formacaoPolicia']['superior'],
                        'curso' => $cursoSuperior?->descricao,
                        'curso_id' => $cursoSuperior?->id,
                        'dataInicio' => $request['formacaoPolicia']['dataInicioSuperior'],
                        'dataConclusao' => isset($request['formacaoPolicia']['dataConclusaoSuperior']) ? $request['formacaoPolicia']['dataConclusaoSuperior'] : null,
                    ]);
            }

            if (isset($request['formacoesComplementares']['cursoComplementar'])) {
                $formacoesComplementares = DB::table('formacoesComplementares')
                    ->insertGetId([
                        'id' => (string) Str::uuid(),
                        'pessoa_id' => $request['formacoesComplementares']['pessoa_id'],
                        'cursoComplementar' => isset($request['formacoesComplementares']['cursoComplementar']) ? $request['formacoesComplementares']['cursoComplementar'] : null,
                        'instituicao' => $request['formacoesComplementares']['instituicaoComplementar'],
                        'anoConlusao' => $request['formacoesComplementares']['anoConclusaoComplementar'],
                        'certificado' => $filename ?? '',
                    ]);
            }

            $pessoaId = $request->input('formacaoAcademica.pessoa_id')
                ?? $request->input('formacaoPolicia.pessoa_id');
            $pessoa = new PessoaController;
            $pessoa->updateStep(2, $pessoaId);

            DB::commit();

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return response()->json(['error' => 'Erro inesperado'], 500);
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
            return response()->json(['error' => 'Error inesperado'.$th], 00);
        }
    }
}
