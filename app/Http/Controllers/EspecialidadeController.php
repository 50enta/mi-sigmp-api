<?php

namespace App\Http\Controllers;

use App\Models\Especialidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EspecialidadeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Especialidade::query();

        $especialidades = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['especialidades' => $especialidades], 200);
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
            'descricao' => 'required|string',
            'detalhes' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar especialidade', 'errors' => $validation->errors()], 409);
        }

        $especialidade = Especialidade::create($validation->validated());

        return response()->json(['message' => 'Especialidade criada com sucesso!', 'especialidade' => $especialidade], 201);
    }

    /**
     * Display the specified resource.
     */    public function show($id)
    {
        $especialidade = Especialidade::findOrFail($id);
        return response()->json($especialidade, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Especialidade $especialidade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $especialidade = Especialidade::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'descricao' => 'required|string',
            'detalhes' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar especialidade', 'errors' => $validation->errors()], 409);
        }

        $especialidade->update($validation->validated());

        return response()->json(['message' => 'Especialidade actualizada com sucesso!', 'especialidade' => $especialidade], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $especialidade = Especialidade::findOrFail($id);
        $especialidade->delete();

        return response()->json(['message' => 'Especialidade eliminada com sucesso!'], 200);
    }
}
