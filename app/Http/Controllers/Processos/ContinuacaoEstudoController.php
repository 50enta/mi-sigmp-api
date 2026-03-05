<?php

namespace App\Http\Controllers\Processos;

use App\Models\ContinuacaoEstudo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ContinuacaoEstudoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = ContinuacaoEstudo::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['continuacao_estudos' => $registros], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'despacho' => 'nullable|string|max:255',
            'instituicao' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:255',
            'nivelPretendido' => 'nullable|string|max:255',
            'situacao' => 'required|in:activo,inactivo',
            'dataInicio' => 'nullable|date',
            'dataPrevisaoTermino' => 'nullable|date',
            'obs' => 'nullable|string',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao registar continuação de estudo', 'errors' => $validation->errors()], 409);
        }

        $registro = ContinuacaoEstudo::create($validation->validated());

        return response()->json(['message' => 'Continuação de estudo registada com sucesso!', 'continuacao_estudo' => $registro], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $registro = ContinuacaoEstudo::findOrFail($id);
        return response()->json($registro, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContinuacaoEstudo $continuacaoEstudo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $registro = ContinuacaoEstudo::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'despacho' => 'nullable|string|max:255',
            'instituicao' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:255',
            'nivelPretendido' => 'nullable|string|max:255',
            'situacao' => 'required|in:activo,inactivo',
            'dataInicio' => 'nullable|date',
            'dataPrevisaoTermino' => 'nullable|date',
            'obs' => 'nullable|string',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar continuação de estudo', 'errors' => $validation->errors()], 409);
        }

        $registro->update($validation->validated());

        return response()->json(['message' => 'Continuação de estudo actualizada com sucesso!', 'continuacao_estudo' => $registro], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = ContinuacaoEstudo::findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Continuação de estudo eliminada com sucesso!'], 200);
    }
}
