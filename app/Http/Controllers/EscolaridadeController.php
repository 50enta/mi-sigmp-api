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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Escolaridade::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['escolaridades' => $registros], 200);
    }


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
                    'dataFim'     => $request['formacaoAcademica']['dataFim'],
                    'certificado' => $certificado ?? null,
                ]);

            $formacaoPolicial = DB::table('formacaoPolicial')
                ->insertGetId([
                    'id' => (string) Str::uuid(),
                    'pessoa_id' => $request['formacaoPolicia']['pessoa_id'],
                    'basico' => isset($request['formacaoPolicia']['basico']) ? $request['formacaoPolicia']['basico'] : null,
                    'dataConclusaoBasico' => isset($request['formacaoPolicia']['dataConclusaoBasico']) ? $request['formacaoPolicia']['dataConclusaoBasico'] : null,
                    'medio' => isset($request['formacaoPolicia']['medio']) ? $request['formacaoPolicia']['medio'] : null,
                    'dataConclusaoMedio' => isset($request['formacaoPolicia']['dataConclusaoMedio']) ? $request['formacaoPolicia']['dataConclusaoMedio'] : null,
                    'superior' => isset($request['formacaoPolicia']['superior']) ? $request['formacaoPolicia']['superior'] : null,
                    'dataConclusaoSuperior' => isset($request['formacaoPolicia']['dataConclusaoSuperior']) ? $request['formacaoPolicia']['dataConclusaoSuperior'] : null,
                ]);

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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $registro = Escolaridade::findOrFail($id);
        return response()->json($registro, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Escolaridade $escolaridade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreFormacaoRequest $request, $id)
    {
        $escolaridade = Escolaridade::findOrFail($id);

        $escolaridade->update($request->validated());

        return response()->json($escolaridade, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = Escolaridade::findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Escolaridade eliminada com sucesso!'], 200);
    }
}
