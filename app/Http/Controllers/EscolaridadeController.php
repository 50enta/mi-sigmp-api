<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormacaoRequest;
use App\Models\FormacaoPolicial;
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

            foreach (['cursoBasico', 'cursoMedio', 'cursoSuperior'] as $campoCurso) {
                $cursoId = $request->input("formacaoPolicia.$campoCurso");
                if ($cursoId) {
                    FormacaoPolicial::create([
                        'pessoa_id' => $request->input('formacaoPolicia.pessoa_id'),
                        'curso_id' => $cursoId,
                    ]);
                }
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
                ->join('cursos', 'cursos.id', '=', 'formacaoPolicial.curso_id')
                ->orderByDesc('cursos.dataFim')
                ->where('formacaoPolicial.pessoa_id', $request->query('pessoa_id'))
                ->select([
                    'formacaoPolicial.id',
                    'formacaoPolicial.pessoa_id',
                    'formacaoPolicial.curso_id',
                    'formacaoPolicial.created_at',
                    'formacaoPolicial.updated_at',
                    'cursos.descricao as curso',
                    'cursos.categoria',
                    'cursos.local',
                    'cursos.dataInicio',
                    'cursos.dataFim as dataConclusao',
                ])
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
