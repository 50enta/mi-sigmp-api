<?php

namespace App\Http\Controllers;

use App\Models\CategoriaPolicia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaPoliciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = CategoriaPolicia::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['categoria_policias' => $registros], 200);
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
            'categoria_id' => 'required|uuid|exists:categorias,id',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'despacho' => 'nullable|string',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar vínculo de categoria', 'errors' => $validation->errors()], 409);
        }

        $registro = CategoriaPolicia::create($validation->validated());

        return response()->json(['message' => 'Categoria policial criada com sucesso!', 'categoria_policia' => $registro], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $registro = CategoriaPolicia::findOrFail($id);
        return response()->json($registro, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CategoriaPolicia $categoriaPolicia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $registro = CategoriaPolicia::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'categoria_id' => 'required|uuid|exists:categorias,id',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'despacho' => 'nullable|string',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar vínculo', 'errors' => $validation->errors()], 409);
        }

        $registro->update($validation->validated());

        return response()->json(['message' => 'Categoria policial actualizada com sucesso!', 'categoria_policia' => $registro], 200);
    }

    /**
     * Remove the specified resource from storage.
     */

     public function destroy($id)
     {
         $registro = CategoriaPolicia::findOrFail($id);
         $registro->delete();
 
         return response()->json(['message' => 'Categoria policial eliminada com sucesso!'], 200);
     }
}
