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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormacaoRequest $request)
    {
        try {
            $fileName = /*time() . '_' .  $request->file('certificadoFormacaoAcademica')->getClientOriginalName()*/ 'The filee';

            // if ($request->file('certificadoFormacaoAcademica')) {
            //     $filename = time() . '_' . $file->getClientOriginalName();
            //     $file->move(public_path('uploads'), $filename);
            // }

            $updateData = $request->except(['formacaoAcademica.certificadoFormacaoAcademica']);
            dd($updateData['formacaoAcademica']['nivel']);

            $formacaoAcademica = DB::table('escolaridades') // substitua pelo nome real da sua tabela
                ->insert([
                    'pessoa_id' => $updateData['formacaoAcademica']['pessoa_id'],
                    'nivel'       => $updateData['formacaoAcademica']['nivel'],
                    'instituicao' => $updateData['formacaoAcademica']['instituicao'],
                    'curso'       => $updateData['formacaoAcademica']['curso'],
                    'dataInicio'  => $updateData['formacaoAcademica']['dataInicio'],
                    'dataFim'     => $updateData['formacaoAcademica']['dataFim'],
                    'certificado' => 'fileName',
                ]);

            $formacaoPolicial = DB::table('formacaoPolicial')
                ->insert([
                    'pessoa_id' => $updateData['formacaoPolicia']['pessoa_id'],
                    'academiaPolicial' => $updateData['formacaoPolicia']['academiaPolicial'],
                    'dataInicio' => $updateData['formacaoPolicia']['dataInicio'],
                    'curso'       => $updateData['formacaoPolicia']['curso'],
                    'dataFim'     => $updateData['formacaoPolicia']['dataFim'],
                ]);

            if (isset($updateData['formacoesComplementares'])) {

                $formacoesComplementares = DB::table('formacoesComplementares')
                    ->insert([
                        'pessoa_id' => $updateData['formacoesComplementares']['pessoa_id'],
                        'cursoComplementar' => $updateData['formacoesComplementares']['cursoComplementar'],
                        'instituicao' => $updateData['formacoesComplementares']['instituicao'],
                        'anoConlusao'       => $updateData['formacoesComplementares']['anoConlusao'],
                        'certificado'     => $updateData['formacoesComplementares']['certificado'],
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
