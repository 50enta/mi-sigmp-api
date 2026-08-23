<?php

namespace App\Http\Controllers;

use App\Models\EscalaoPolicia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EscalaoPoliciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = EscalaoPolicia::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['escalao_policias' => $registros], 200);
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
            'escala_id' => 'required|uuid|exists:escalas,id',
            'pessoa_id' => 'required|exists:pessoas,id',
            'despacho' => 'nullable|string|max:255',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar vínculo de escala', 'errors' => $validation->errors()], 409);
        }

        $registro = EscalaoPolicia::create($validation->validated());

        return response()->json(['message' => 'Escala policial criada com sucesso!', 'escalao_policia' => $registro], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $registro = EscalaoPolicia::findOrFail($id);
        return response()->json($registro, 200);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EscalaoPolicia $escalaoPolicia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $registro = EscalaoPolicia::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'escala_id' => 'required|uuid|exists:escalas,id',
            'pessoa_id' => 'required|exists:pessoas,id',
            'despacho' => 'nullable|string|max:255',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar vínculo de escala', 'errors' => $validation->errors()], 409);
        }

        $registro->update($validation->validated());

        return response()->json(['message' => 'Escala policial actualizada com sucesso!', 'escalao_policia' => $registro], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = EscalaoPolicia::findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Escalao eliminada com sucesso!'], 200);
    }
}
