<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Curso::query();

        $cursos = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['cursos' => $cursos], 200);
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
            'descricao' => 'required|string',
            'dataInicio' => 'nullable|date',
            'especialidade' => 'nullable|string',
            'dataFim' => 'nullable|date',
            'numero' => 'nullable|string',
            'grau' => 'nullable|string',
            'local' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar o curso', 'errors' => $validation->errors()], 409);
        }

        $curso = Curso::create($validation->validated());

        return response()->json(['message' => 'Curso criado com sucesso!', 'curso' => $curso], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $curso = Curso::findOrFail($id);
        return response()->json($curso, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Curso $curso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'descricao' => 'required|string',
            'dataInicio' => 'nullable|date',
            'especialidade' => 'nullable|string',
            'dataFim' => 'nullable|date',
            'numero' => 'nullable|string',
            'grau' => 'nullable|string',
            'local' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar o curso', 'errors' => $validation->errors()], 409);
        }

        $curso->update($validation->validated());

        return response()->json(['message' => 'Curso actualizado com sucesso!', 'curso' => $curso], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();

        return response()->json(['message' => 'Curso eliminado com sucesso!'], 200);
    }
}
