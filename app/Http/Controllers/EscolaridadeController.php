<?php

namespace App\Http\Controllers;

use App\Http\Requests\Requests\escolaidadeRequest;
use App\Http\Requests\Requests\StoreFormacaoRequest as RequestsStoreFormacaoRequest;
use App\Http\Requests\StoreFormacaoRequest;
use App\Models\Escolaridade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
                $filename = time() . '_' . $request->file('formacoesComplementares.certificadoComplementar')->getClientOriginalName();
                $request->file('formacoesComplementares.certificadoComplementar')->move(public_path('uploads'), $filename);
            }

            if ($request->file('formacoesComplementares.certificadoComplementar')) {
                $filename = time() . '_' . $request->file('formacoesComplementares.certificadoComplementar')->getClientOriginalName();
                $request->file('formacoesComplementares.certificadoComplementar')->move(public_path('uploads'), $filename);
            }

            $formacaoAcademica = DB::table('escolaridades') // substitua pelo nome real da sua tabela
                ->insert([
                    'pessoa_id' =>$request['formacaoAcademica']['pessoa_id'],
                    'nivel'       =>$request['formacaoAcademica']['nivel'],
                    'instituicao' =>$request['formacaoAcademica']['instituicao'],
                    'curso'       =>$request['formacaoAcademica']['curso'],
                    'dataInicio'  =>$request['formacaoAcademica']['dataInicio'],
                    'dataFim'     =>$request['formacaoAcademica']['dataFim'],
                    'certificado' => $filename,
                ]);

            $formacaoPolicial = DB::table('formacaoPolicial')
                ->insert([
                    'pessoa_id' =>$request['formacaoPolicia']['pessoa_id'],
                    'basico' =>$request['formacaoPolicia']['basico'],
                    'dataConclusaoBasico' =>$request['formacaoPolicia']['dataConclusaoBasico'],
                    'medio' =>$request['formacaoPolicia']['medio'],
                    'dataConclusaoMedio' =>$request['formacaoPolicia']['dataConclusaoMedio'],
                    'superior' =>$request['formacaoPolicia']['superior'],
                    'dataConclusaoSuperior' =>$request['formacaoPolicia']['dataConclusaoSuperior'],
                ]);

            if (isset($updateData['formacoesComplementares'])) {

                $formacoesComplementares = DB::table('formacoesComplementares')
                    ->insert([
                        'pessoa_id' =>$request['formacoesComplementares']['pessoa_id'],
                        'cursoComplementar' =>$request['formacoesComplementares']['cursoComplementar'],
                        'instituicao' =>$request['formacoesComplementares']['instituicao'],
                        'anoConlusao'       =>$request['formacoesComplementares']['anoConlusao'],
                        'certificado'     =>$request['formacoesComplementares']['certificadoComplementar'],
                    ]);
            }

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
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
    public function update(Request $request, $id) {}

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
