<?php

namespace App\Http\Controllers;

use App\Http\Requests\Requests\escolaidadeRequest;
use App\Models\Escolaridade;
use Illuminate\Http\Request;
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
    public function store(Request $request)
    {
        try {
            if($request->file('certificadoFormacaoAcademica')){
                
            }
            //code...
            dd($request->formacaoAcademica);
            // $registro = Escolaridade::create($request->all());

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
    public function update(Request $request, $id)
    {
        $registro = Escolaridade::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'nivel' => 'nullable|in:elementar,basico,medio,licenciatura,mestrado,phd',
            'instituicao' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:255',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date',
            'isConcluido' => 'required|boolean',
            'obs' => 'nullable|string',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar escolaridade', 'errors' => $validation->errors()], 409);
        }

        $registro->update($validation->validated());

        return response()->json(['message' => 'Escolaridade actualizada com sucesso!', 'escolaridade' => $registro], 200);
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
