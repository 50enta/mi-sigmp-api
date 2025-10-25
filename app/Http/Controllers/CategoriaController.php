<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Categoria::query();

        $categorias = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['categorias' => $categorias], 200);
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
            'comentarios' => 'nullable|string',
            'activo' => 'required|boolean',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Erro ao criar a categoria',
                'errors' => $validation->errors()
            ], 409);
        }

        $categoria = Categoria::create($validation->validated());

        return response()->json([
            'message' => 'Categoria criada com sucesso!',
            'categoria' => $categoria
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $categoria = Categoria::findOrFail($id);
        return response()->json($categoria, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'descricao' => 'required|string',
            'comentarios' => 'nullable|string',
            'activo' => 'required|boolean',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar a categoria', 'errors' => $validation->errors()], 409);
        }

        $categoria->update($validation->validated());

        return response()->json(['message' => 'Categoria actualizada com sucesso!', 'categoria' => $categoria], 200);
    }

    /**
     * Remove the specified resource from storage.
     */    
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->delete();

        return response()->json(['message' => 'Categoria eliminada com sucesso!'], 200);
    }
}
