<?php

namespace App\Http\Controllers;

use App\Models\CursoPolicia;
use Illuminate\Http\Request;

class CursoPoliciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = CursoPolicia::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['curso_policias' => $registros], 200);
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
            'curso_id' => 'required|uuid|exists:cursos,id',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'despacho_admissao' => 'nullable|string',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar curso policial', 'errors' => $validation->errors()], 409);
        }

        $registro = CursoPolicia::create($validation->validated());

        return response()->json(['message' => 'Curso policial criado com sucesso!', 'curso_policia' => $registro], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $registro = CursoPolicia::findOrFail($id);
        return response()->json($registro, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CursoPolicia $cursoPolicia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $registro = CursoPolicia::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'curso_id' => 'required|uuid|exists:cursos,id',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'despacho_admissao' => 'nullable|string',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar curso policial', 'errors' => $validation->errors()], 409);
        }

        $registro->update($validation->validated());

        return response()->json(['message' => 'Actualizado com sucesso!', 'curso_policia' => $registro], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = CursoPolicia::findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Curso policial eliminado com sucesso!'], 200);
    }
}
