<?php

namespace App\Http\Controllers;

use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Local::query();

        $locais = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['locais' => $locais], 200);
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
            'isLogico' => 'required|boolean',
            'nivel' => 'required|integer',
            'descricao' => 'required|string',
            'comentarios' => 'nullable|string',
            'hasPai' => 'required|boolean',
            'pai_id' => 'nullable|uuid|exists:locais,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar o local', 'errors' => $validation->errors()], 409);
        }

        $local = Local::create($validation->validated());

        return response()->json(['message' => 'Local criado com sucesso!', 'local' => $local], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $local = Local::findOrFail($id);
        return response()->json($local, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Local $local)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $local = Local::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'isLogico' => 'required|boolean',
            'nivel' => 'required|integer',
            'descricao' => 'required|string',
            'comentarios' => 'nullable|string',
            'hasPai' => 'required|boolean',
            'pai_id' => 'nullable|uuid|exists:locais,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar o local', 'errors' => $validation->errors()], 409);
        }

        $local->update($validation->validated());

        return response()->json(['message' => 'Local actualizado com sucesso!', 'local' => $local], 200);
    }

    /**
     * Remove the specified resource from storage.
     */    public function destroy($id)
    {
        $local = Local::findOrFail($id);
        $local->delete();

        return response()->json(['message' => 'Local eliminado com sucesso!'], 200);
    }
}
